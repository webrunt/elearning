<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Support\Collection;

class LessonQuizSession
{
    public const QUESTIONS_PER_ATTEMPT = 3;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function randomQuestionsForClient(Lesson $lesson): array
    {
        $questions = $lesson->quizQuestions()
            ->with('options')
            ->inRandomOrder()
            ->limit(self::QUESTIONS_PER_ATTEMPT)
            ->get();

        return $questions->map(fn (QuizQuestion $question) => [
            'id' => $question->id,
            'prompt' => $question->prompt,
            'options' => $question->options->map(fn (QuizOption $option) => [
                'id' => $option->id,
                'label' => $option->label,
            ]),
        ])->values()->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $answersInput
     * @return array{score: int, passed: bool, pass_percent: int}
     */
    public function gradeAttempt(Lesson $lesson, array $answersInput): array
    {
        $questionIds = [];
        foreach ($answersInput as $answer) {
            if (isset($answer['question_id'])) {
                $questionIds[] = (int) $answer['question_id'];
            }
        }

        $questions = $lesson->quizQuestions()
            ->with('options')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $total = $questions->count();
        $correct = 0;
        $gradedAnswers = [];

        foreach ($answersInput as $answer) {
            $questionId = (int) ($answer['question_id'] ?? 0);
            $optionId = (int) ($answer['option_id'] ?? 0);
            $question = $questions->get($questionId);

            if ($question === null) {
                continue;
            }

            $selected = $question->options->firstWhere('id', $optionId);
            $isCorrect = $selected !== null && $selected->is_correct;

            if ($isCorrect) {
                $correct++;
            }

            $gradedAnswers[] = [
                'question_id' => $questionId,
                'option_id' => $optionId,
                'is_correct' => $isCorrect,
            ];
        }

        $score = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
        $passPercent = $lesson->quiz_pass_percent > 0 ? $lesson->quiz_pass_percent : 70;
        $passed = $score >= $passPercent;

        return [
            'score' => $score,
            'passed' => $passed,
            'pass_percent' => $passPercent,
            'graded_answers' => $gradedAnswers,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $gradedAnswers
     */
    public function storeAttempt(
        Lesson $lesson,
        int $userId,
        ?int $enrollmentId,
        int $score,
        bool $passed,
        array $gradedAnswers
    ): QuizAttempt {
        $attempt = QuizAttempt::create([
            'user_id' => $userId,
            'lesson_id' => $lesson->id,
            'enrollment_id' => $enrollmentId,
            'score' => $score,
            'passed' => $passed,
            'completed_at' => now(),
        ]);

        foreach ($gradedAnswers as $answer) {
            QuizAttemptAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $answer['question_id'],
                'quiz_option_id' => $answer['option_id'],
            ]);
        }

        return $attempt;
    }
}
