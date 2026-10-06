
function showBanner(beforeEl, message, type) {
    const colors = {
        success: { bg: '#d4edda', text: '#155724' },
        error:   { bg: '#f8d7da', text: '#721c24' },
        warning: { bg: '#fff3cd', text: '#856404' },
        info:    { bg: '#d1ecf1', text: '#0c5460' },
    };
    const c = colors[type] || colors.info;

    const box = document.createElement('p');
    box.textContent = message;
    box.style.background = c.bg;
    box.style.color = c.text;
    box.style.padding = '12px 16px';
    box.style.borderRadius = '8px';
    box.style.marginBottom = '16px';
    box.style.fontSize = '14px';
    beforeEl.insertAdjacentElement('beforebegin', box);
    return box;
}

document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (password && confirmPassword) {
        const warning = document.createElement('small');
        warning.style.color = '#b71c1c';
        warning.style.display = 'none';
        warning.textContent = 'Passwords do not match.';
        confirmPassword.insertAdjacentElement('afterend', warning);

        function checkPasswordsMatch() {
            if (confirmPassword.value.length === 0) {
                warning.style.display = 'none';
                return;
            }
            warning.style.display = (password.value !== confirmPassword.value) ? 'block' : 'none';
        }

        password.addEventListener('input', checkPasswordsMatch);
        confirmPassword.addEventListener('input', checkPasswordsMatch);

        document.querySelector('form')?.addEventListener('submit', function (e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                warning.style.display = 'block';
                confirmPassword.focus();
            }
        });
    }

    const newPassword = document.getElementById('new_password');
    const confirmNewPassword = document.getElementById('confirm_password');

    if (newPassword && confirmNewPassword && !password) {
        const warning2 = document.createElement('small');
        warning2.style.color = '#b71c1c';
        warning2.style.display = 'none';
        warning2.textContent = 'Passwords do not match.';
        confirmNewPassword.insertAdjacentElement('afterend', warning2);

        function checkNewPasswordsMatch() {
            if (confirmNewPassword.value.length === 0) {
                warning2.style.display = 'none';
                return;
            }
            warning2.style.display = (newPassword.value !== confirmNewPassword.value) ? 'block' : 'none';
        }

        newPassword.addEventListener('input', checkNewPasswordsMatch);
        confirmNewPassword.addEventListener('input', checkNewPasswordsMatch);

        document.querySelector('form')?.addEventListener('submit', function (e) {
            if (newPassword.value !== confirmNewPassword.value) {
                e.preventDefault();
                warning2.style.display = 'block';
                confirmNewPassword.focus();
            }
        });
    }

    
    const ageField = document.getElementById('age');
    const roleField = document.getElementById('role');

    if (ageField && roleField) {
        const ageWarning = document.createElement('small');
        ageWarning.style.color = '#b71c1c';
        ageWarning.style.display = 'none';
        ageWarning.textContent = 'Donors must be between 18 and 65 years old.';
        ageField.insertAdjacentElement('afterend', ageWarning);

        ageField.addEventListener('input', function () {
            const age = parseInt(ageField.value, 10);
            const isDonor = roleField.value === 'donor';
            ageWarning.style.display = (isDonor && (age < 18 || age > 65)) ? 'block' : 'none';
        });
    }


    const starWidget = document.querySelector('.star-rating');

    if (starWidget) {
        const stars = starWidget.querySelectorAll('span');
        const ratingInput = document.getElementById('rating_value');

        function paintStars(count) {
            stars.forEach(function (star, index) {
                star.textContent = index < count ? '★' : '☆';
            });
        }

        stars.forEach(function (star, index) {
            star.addEventListener('click', function () {
                ratingInput.value = index + 1;
                paintStars(index + 1);
            });
            star.addEventListener('mouseover', function () {
                paintStars(index + 1);
            });
        });

        starWidget.addEventListener('mouseleave', function () {
            paintStars(parseInt(ratingInput.value, 10) || 0);
        });
    }


    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('show');
        });
    }

    
    document.querySelectorAll('.confirm-action').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const message = link.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    
    const contactForm = document.getElementById('contact-form');

    if (contactForm) {
        const params = new URLSearchParams(window.location.search);

        if (params.get('sent') === '1') {
            showBanner(contactForm, "Thank you for your support! Your message has been received - we'll get back to you soon.", 'success');
            contactForm.reset();
        }
        if (params.get('sent') === '0') {
            showBanner(contactForm, 'Please fill in every field before sending your message.', 'error');
        }
    }

    
    const params = new URLSearchParams(window.location.search);

    const registerForm = document.getElementById('register-form');
    if (registerForm && params.get('error')) {
        showBanner(registerForm, params.get('error'), 'error');
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        if (params.get('error')) {
            showBanner(loginForm, params.get('error'), 'error');
        }
        if (params.get('registered') === '1') {
            showBanner(loginForm, 'Registration successful! Your account is now waiting on admin approval before you can log in.', 'success');
        }
    }

});


window.addEventListener( 'pageshow', function (event){
    if (event.persisted || (performance.navigation && performance.navigation.type==2)){
        window.location.reload();
    }
});