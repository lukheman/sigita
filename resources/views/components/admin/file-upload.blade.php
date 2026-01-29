@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'accept' => 'image/*',
    'multiple' => false,
    'maxSize' => '2MB',
    'hint' => null,
    'error' => null,
    'preview' => true,
])

@php
    $inputId = $id ?? $name ?? 'file-upload-' . uniqid();
@endphp

<div class="file-upload-wrapper" x-data="{ 
    files: [], 
    dragging: false,
    handleFiles(event) {
        const newFiles = event.target.files || event.dataTransfer.files;
        for (let i = 0; i < newFiles.length; i++) {
            this.files.push({
                file: newFiles[i],
                preview: URL.createObjectURL(newFiles[i]),
                name: newFiles[i].name,
                size: (newFiles[i].size / 1024 / 1024).toFixed(2) + ' MB'
            });
        }
    },
    removeFile(index) {
        this.files.splice(index, 1);
    }
}">
    @if($label)
        <label class="form-label">
            {{ $label }}
        </label>
    @endif

    <div 
        class="file-upload-zone {{ $error ? 'is-invalid' : '' }}"
        :class="{ 'dragging': dragging }"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="dragging = false; handleFiles($event)"
        @click="$refs.fileInput.click()"
    >
        <input 
            type="file" 
            id="{{ $inputId }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            x-ref="fileInput"
            @change="handleFiles($event)"
            class="d-none"
            {{ $attributes }}
        >

        <div class="file-upload-content">
            <i class="fas fa-cloud-upload-alt mb-3" style="font-size: 2.5rem; color: var(--primary-color);"></i>
            <p class="mb-1" style="color: var(--text-primary); font-weight: 500;">
                Drag & drop file di sini atau <span style="color: var(--primary-color);">browse</span>
            </p>
            <small style="color: var(--text-muted);">
                {{ $accept }} • Maksimal {{ $maxSize }}
            </small>
        </div>
    </div>

    @if($error)
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif

    @if($hint && !$error)
        <div class="form-text" style="color: var(--text-muted);">{{ $hint }}</div>
    @endif

    {{-- Preview Section --}}
    @if($preview)
        <template x-if="files.length > 0">
            <div class="file-preview-list mt-3">
                <template x-for="(file, index) in files" :key="index">
                    <div class="file-preview-item d-flex align-items-center gap-3 p-3 mb-2" style="background: var(--bg-tertiary); border-radius: 12px;">
                        <template x-if="file.file.type.startsWith('image/')">
                            <img :src="file.preview" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                        </template>
                        <template x-if="!file.file.type.startsWith('image/')">
                            <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--primary-color); border-radius: 8px; color: white;">
                                <i class="fas fa-file"></i>
                            </div>
                        </template>
                        <div class="flex-grow-1">
                            <div style="color: var(--text-primary); font-weight: 500;" x-text="file.name"></div>
                            <small style="color: var(--text-muted);" x-text="file.size"></small>
                        </div>
                        <button type="button" class="btn btn-link" style="color: var(--danger-color);" @click="removeFile(index)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
            </div>
        </template>
    @endif
</div>

<style>
    .file-upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--bg-secondary);
    }

    .file-upload-zone:hover,
    .file-upload-zone.dragging {
        border-color: var(--primary-color);
        background: var(--hover-bg);
    }

    .file-upload-zone.is-invalid {
        border-color: var(--danger-color);
    }

    .file-upload-content i {
        transition: transform 0.3s;
    }

    .file-upload-zone:hover .file-upload-content i,
    .file-upload-zone.dragging .file-upload-content i {
        transform: translateY(-5px);
    }
</style>
