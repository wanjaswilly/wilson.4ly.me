import '../css/app.css';

// console.log('Tailwind CSS v4 + Vite is working!');

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';

document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.getElementById('editor');
    const contentInput = document.getElementById('content');

    if (!editorElement || !contentInput) return;

    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit,
            Image,
            Link.configure({
                openOnClick: false,
            }),
        ],
        content: contentInput.value,
        onUpdate: ({ editor }) => {
            contentInput.value = editor.getHTML();
        },
    });

    // Handle image upload
    function handleImageUpload(file) {
        return new Promise(resolve => {
            const formData = new FormData();
            formData.append('image', file);

            fetch('/upload-image', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => resolve(data.url))
                .catch(() => {
                    // Fallback to base64 if upload fails
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });
        });
    }

    // Add image button
    const imageInput = document.createElement('input');
    imageInput.type = 'file';
    imageInput.accept = 'image/*';
    imageInput.style.display = 'none';
    imageInput.onchange = async () => {
        const file = imageInput.files[0];
        if (file) {
            const url = await handleImageUpload(file);
            editor.chain().focus().setImage({ src: url }).run();
        }
    };
    document.body.appendChild(imageInput);

    // Toolbar buttons
    document.querySelectorAll('[data-tiptap-command]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const command = this.getAttribute('data-tiptap-command');

            if (command === 'image') {
                imageInput.click();
                return;
            }

            editor.chain().focus()[command]().run();
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('mobile-menu-button')
    const menu = document.getElementById('mobile-menu')
    const iconOpen = document.getElementById('menu-open')
    const iconClose = document.getElementById('menu-close')

    button.addEventListener('click', () => {
        menu.classList.toggle('hidden')
        iconOpen.classList.toggle('hidden')
        iconClose.classList.toggle('hidden')
    });
});
