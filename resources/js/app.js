

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('accessCodeInput', () => ({
    digits: Array(6).fill(''),

    updateDigit(index, event) {
        const pastedDigits = event.target.value.replace(/\D/g, '').slice(0, 6);

        if (pastedDigits.length > 1) {
            pastedDigits.split('').forEach((digit, offset) => {
                if (index + offset < this.digits.length) this.digits[index + offset] = digit;
            });
            event.target.value = this.digits[index];
            this.focusDigit(Math.min(index + pastedDigits.length, this.digits.length - 1));

            return;
        }

        this.digits[index] = pastedDigits;
        event.target.value = pastedDigits;
        if (pastedDigits && index < this.digits.length - 1) this.focusDigit(index + 1);
    },

    pasteCode(event, index) {
        const pastedDigits = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        if (!pastedDigits) return;

        event.preventDefault();
        const start = pastedDigits.length === this.digits.length ? 0 : index;
        pastedDigits.split('').forEach((digit, offset) => {
            if (start + offset < this.digits.length) this.digits[start + offset] = digit;
        });
        this.focusDigit(Math.min(start + pastedDigits.length, this.digits.length - 1));
    },

    handleKeydown(index, event) {
        if (event.key === 'Backspace' && !this.digits[index] && index > 0) this.focusDigit(index - 1);
        if (event.key === 'ArrowLeft' && index > 0) this.focusDigit(index - 1);
        if (event.key === 'ArrowRight' && index < this.digits.length - 1) this.focusDigit(index + 1);
    },

    focusDigit(index) {
        this.$root.querySelector(`[data-code-index="${index}"]`)?.focus();
    },
}));

Alpine.data('formSubmitState', () => ({
    submitting: false,

    submitOnce(event) {
        if (this.submitting) {
            event.preventDefault();
            return;
        }

        this.submitting = true;
    },
}));

Alpine.data('ballotWizard', (positions, initialChoices, initialStep, positionLabel) => ({
    positions,
    choices: initialChoices,
    current: initialStep,
    positionLabel,
    showError: false,
    manifestoCandidate: null,
    manifestoTrigger: null,

    init() {
        this.focusCurrentPosition();
        this.$watch('current', () => this.focusCurrentPosition());
    },

    get allSelected() {
        return this.positions.every((position) => this.choices[position.id]);
    },

    initials(name) {
        return name.split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0)).join('').toUpperCase();
    },

    select(positionId, candidateId) {
        this.choices[positionId] = candidateId;
        this.showError = false;
    },

    positionText() {
        return this.positionLabel
            .replace(':current', this.current + 1)
            .replace(':total', this.positions.length);
    },

    focusCurrentPosition() {
        const position = this.positions[this.current];

        if (position) {
            this.$nextTick(() => this.$root.querySelector(`#position-title-${position.id}`)?.focus());
        }
    },

    continueToNextPosition() {
        if (!this.choices[this.positions[this.current].id]) {
            this.showError = true;
            return;
        }

        this.current++;
    },

    submitReview(form) {
        if (!this.allSelected) {
            this.showError = true;
            return;
        }

        form.requestSubmit();
    },

    openManifesto(candidate, trigger) {
        this.manifestoCandidate = candidate;
        this.manifestoTrigger = trigger;
        this.$nextTick(() => this.$refs.manifestoClose?.focus());
    },

    closeManifesto() {
        this.manifestoCandidate = null;
        this.$nextTick(() => this.manifestoTrigger?.focus());
    },
}));

Alpine.data('receiptCopy', (reference, labels) => ({
    reference,
    labels,
    copyState: 'idle',

    async copy() {
        try {
            await navigator.clipboard.writeText(this.reference);
            this.copyState = 'copied';
        } catch {
            this.copyState = 'failed';
        }
    },
}));

Alpine.data('photoUploader', (initialPhoto = null) => ({
    initialPhoto,
    preview: initialPhoto,
    objectUrl: null,
    removePhoto: false,

    handlePhotoChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        this.releaseObjectUrl();
        this.objectUrl = URL.createObjectURL(file);
        this.preview = this.objectUrl;
        this.removePhoto = false;
    },

    togglePhotoRemoval() {
        if (this.removePhoto) {
            this.releaseObjectUrl();
            if (this.$refs.photoInput) this.$refs.photoInput.value = '';
        }

        this.preview = this.removePhoto ? null : this.initialPhoto;
    },

    clearPhotoSelection() {
        this.releaseObjectUrl();
        this.$refs.photoInput.value = '';
        this.preview = this.removePhoto ? null : this.initialPhoto;
    },

    releaseObjectUrl() {
        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = null;
    },

    destroy() {
        this.releaseObjectUrl();
    },
}));

Alpine.start();

document.documentElement.classList.add('motion-ready');
const revealItems = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealItems.forEach((item) => observer.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}
