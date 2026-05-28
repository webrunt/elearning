import {
    AutoLink,
    BlockQuote,
    Bold,
    ClassicEditor,
    Essentials,
    Heading,
    Italic,
    Link,
    List,
    Paragraph,
} from 'ckeditor5';

const sharedPlugins = [
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Link,
    AutoLink,
    List,
];

/**
 * @param {'full' | 'minimal'} variant
 * @param {string} placeholder
 * @returns {import('ckeditor5').EditorConfig}
 */
export function getCKEditorConfig(variant, placeholder) {
    const minimal = variant === 'minimal';

    return {
        licenseKey: 'GPL',
        plugins: minimal
            ? sharedPlugins
            : [Essentials, Paragraph, Bold, Italic, Heading, Link, AutoLink, List, BlockQuote],
        toolbar: minimal
            ? ['bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
            : [
                'heading',
                '|',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                'blockQuote',
                '|',
                'undo',
                'redo',
            ],
        placeholder: placeholder || '',
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading3', view: 'h3', title: 'Heading', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Subheading', class: 'ck-heading_heading4' },
            ],
        },
    };
}

export { ClassicEditor };
