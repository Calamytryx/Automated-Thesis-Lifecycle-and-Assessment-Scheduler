/**
 * Shared Validation Utilities
 * Used across dashboard and home page for consistent validation
 */

const ValidationUtils = {
    // Check for emojis
    containsEmoji: function (text) {
        const emojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u;
        return emojiRegex.test(text);
    },

    // Check if text is too long
    isTooLong: function (text, maxLength = 100) {
        return text.length > maxLength;
    },

    // Check if text is too short (single character/digit)
    isTooShort: function (text, minLength = 2) {
        return text.trim().length < minLength;
    },

    // Check for only numbers (for name fields)
    isOnlyNumbers: function (text) {
        return /^\d+$/.test(text.trim());
    },

    // Check for special characters (allow only letters, numbers, spaces, basic punctuation)
    hasInvalidCharacters: function (text) {
        const validPattern = /^[a-zA-Z0-9\s\.\,\-\_\@\(\)]+$/;
        return !validPattern.test(text);
    },

    // Check for HTML tags
    containsHTML: function (text) {
        const htmlRegex = /<[^>]*>/;
        return htmlRegex.test(text);
    },

    // Check if email is valid
    isValidEmail: function (email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    },

    // Validate field based on type
    validateField: function (element) {
        if (!element) return false;
        
        const fieldName = element.name || element.id;
        const value = element.value;
        const errors = this.getFieldErrors(fieldName, value);
        
        if (errors.length > 0) {
            this.showFieldError(element, errors[0]);
            return false;
        } else {
            this.clearFieldError(element);
            return true;
        }
    },

    // Get field validation errors
    getFieldErrors: function(fieldName, value) {
        const errors = [];

        if (!value || value.trim() === '') {
            return ['This field is required'];
        }

        const trimmedValue = value.trim();

        // Check for emojis
        if (this.containsEmoji(trimmedValue)) {
            errors.push('Emojis are not allowed');
        }

        // Check for HTML
        if (this.containsHTML(trimmedValue)) {
            errors.push('HTML tags are not allowed');
        }

        // Field-specific validations
        switch (fieldName) {
            case 'researchTitle':
                if (this.isTooShort(trimmedValue, 10)) {
                    errors.push('Research title must be at least 10 characters long');
                }
                if (this.isTooLong(trimmedValue, 250)) {
                    errors.push('Research title cannot exceed 250 characters');
                }
                break;

            case 'researchField':
                if (this.isTooShort(trimmedValue, 2)) {
                    errors.push('Research field must be at least 2 characters long');
                }
                if (this.isTooLong(trimmedValue, 100)) {
                    errors.push('Research field cannot exceed 100 characters');
                }
                if (this.isOnlyNumbers(trimmedValue)) {
                    errors.push('Research field cannot be only numbers');
                }
                break;

            case 'problem':
                if (this.isTooShort(trimmedValue, 10)) {
                    errors.push('Problem statement must be at least 10 characters long');
                }
                if (this.isTooLong(trimmedValue, 500)) {
                    errors.push('Problem statement cannot exceed 500 characters');
                }
                break;

            case 'email':
                if (!this.isValidEmail(trimmedValue)) {
                    errors.push('Please enter a valid email address');
                }
                break;

            case 'first_name':
            case 'last_name':
                if (this.isTooShort(trimmedValue)) {
                    errors.push('Name must be at least 2 characters long');
                }
                if (this.isTooLong(trimmedValue, 50)) {
                    errors.push('Name cannot exceed 50 characters');
                }
                if (this.isOnlyNumbers(trimmedValue)) {
                    errors.push('Name cannot be only numbers');
                }
                if (this.hasInvalidCharacters(trimmedValue)) {
                    errors.push('Name contains invalid characters');
                }
                break;

            case 'bio':
                if (this.isTooLong(trimmedValue, 500)) {
                    errors.push('Bio cannot exceed 500 characters');
                }
                // Check for symbols (non-alphanumeric, non-space, non-basic punctuation)
                if (/[^a-zA-Z0-9\s\.\,\-\_\@\(\)\!\?]/.test(trimmedValue)) {
                    errors.push('Bio must be alphanumeric and basic punctuation only');
                }
                break;

            case 'headline':
                if (this.isTooLong(trimmedValue, 150)) {
                    errors.push('Headline cannot exceed 150 characters');
                }
                break;
        }

        return errors;
    },

    // Show field error
    showFieldError: function(element, message) {
        element.classList.add('is-invalid');
        
        // Find or create feedback element
        let feedback = element.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            element.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    },

    // Clear field error
    clearFieldError: function(element) {
        element.classList.remove('is-invalid');
        const feedback = element.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    },

    // Setup real-time validation for specific fields
    setupRealTimeValidation: function() {
        const fields = ['researchTitle', 'researchField', 'problem'];
        
        fields.forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (input) {
                // Real-time validation on blur
                input.addEventListener('blur', function() {
                    ValidationUtils.validateField(this);
                });
                
                // Clear validation on input
                input.addEventListener('input', function() {
                    ValidationUtils.clearFieldError(this);
                });
            }
        });
    },

    // Validate entire form (used by other functions)
    validateForm: function() {
        let isValid = true;
        const fields = ['researchTitle', 'researchField', 'problem'];
        
        fields.forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (input && !this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
};
