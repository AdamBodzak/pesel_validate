/**
 * Registration form behaviour:
 * - PESEL hint: decodes birth date and gender (POST /pesel/decode) and offers to fill the fields in,
 *   never overwriting them on its own,
 * - clears stale server-side errors of a field as soon as the user edits it,
 * - blocks double submission.
 *
 * All PESEL rules and texts come from the server - this component only triggers the request
 * and displays the response. The form works without it (progressive enhancement).
 */
const PESEL_LENGTH = 11;

export default ({ decodeUrl, csrfToken, birthDateId, genderName }) => ({
    submitting: false,
    decoded: null,
    error: null,
    birthDate: '',
    gender: '',
    abortController: null,

    init() {
        this.syncFields();

        // After a rejected submission the PESEL field is already filled in
        const peselInput = this.$root.querySelector('[data-pesel-input]');
        if (peselInput && peselInput.value) {
            this.decode(peselInput.value);
        }
    },

    get mismatch() {
        if (!this.decoded) {
            return false;
        }

        // Dates are compared as "Y-m-d" strings - the same format as the date input value
        return (this.birthDate !== '' && this.birthDate !== this.decoded.birthDate)
            || (this.gender !== '' && this.gender !== this.decoded.gender);
    },

    /**
     * Server-side errors describe the previously submitted value - once the field is edited they are stale.
     * The server validates everything again on the next submission.
     */
    clearServerErrors(field) {
        const row = field.closest('[data-form-row]');
        if (!row) {
            return;
        }

        row.querySelectorAll('[data-form-errors]').forEach((errors) => errors.remove());
        row.querySelectorAll('[aria-invalid="true"]').forEach((input) => input.removeAttribute('aria-invalid'));
    },

    syncFields() {
        this.birthDate = document.getElementById(birthDateId)?.value ?? '';
        this.gender = this.$root.querySelector(`input[name="${genderName}"]:checked`)?.value ?? '';
    },

    async decode(value) {
        this.abortController?.abort();
        this.decoded = null;
        this.error = null;

        // Only a trigger condition, not validation - the server decides whether the PESEL is valid
        if (value.replace(/\s+/g, '').length !== PESEL_LENGTH) {
            return;
        }

        this.abortController = new AbortController();

        try {
            const response = await fetch(decodeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify({ pesel: value }),
                signal: this.abortController.signal,
            });
            const payload = await response.json();

            if (response.ok) {
                this.decoded = payload;
            } else if (response.status === 422) {
                this.error = payload.message;
            }
        } catch (exception) {
            // Aborted by a newer request or a network error - the hint is optional, the form still works
        }
    },

    fill() {
        if (!this.decoded) {
            return;
        }

        const birthDateInput = document.getElementById(birthDateId);
        if (birthDateInput) {
            birthDateInput.value = this.decoded.birthDate;
        }

        const genderRadios = this.$root.querySelectorAll(`input[name="${genderName}"]`);
        genderRadios.forEach((radio) => {
            radio.checked = radio.value === this.decoded.gender;
        });

        // Programmatic changes do not fire input/change events - clear the errors of the filled fields explicitly
        [birthDateInput, genderRadios[0]].filter(Boolean).forEach((field) => this.clearServerErrors(field));
        this.syncFields();
    },
});
