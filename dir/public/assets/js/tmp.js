document.addEventListener('DOMContentLoaded', () => {
    const step1Form = document.getElementById('recoverStep1');
    const step2Form = document.getElementById('recoverStep2');
    const hiddenEmail = document.getElementById('hiddenEmail');
    const recoveryEmail = document.getElementById('recoveryEmail');

    if (step1Form) {
        step1Form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-primary');
            const originalText = btn.innerText;
            btn.innerText = 'Enviando...';

            const formData = new FormData(this);

            fetch(this.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    alert("MODO PRUEBA - Código de recuperación temporal: " + res.codigo_prueba);
                    
                    hiddenEmail.value = recoveryEmail.value;
                    
                    step1Form.classList.remove('active');
                    step2Form.classList.add('active');
                } else {
                    btn.style.background = '#e57373';
                    btn.innerText = res.message || 'Error';
                    setTimeout(() => {
                        btn.style.background = '';
                        btn.innerText = originalText;
                    }, 2000);
                }
            })
            .catch(err => {
                btn.innerText = originalText;
            });
        });
    }

    if (step2Form) {
        step2Form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-primary');
            const originalText = btn.innerText;
            btn.innerText = 'Verificando...';

            const formData = new FormData(this);

            fetch(this.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    btn.style.background = '#81c784';
                    btn.innerText = 'Contraseña Actualizada';
                    setTimeout(() => {
                        window.location.href = 'login.php?mode=login';
                    }, 2000);
                } else {
                    btn.style.background = '#e57373';
                    btn.innerText = res.message || 'Código incorrecto';
                    setTimeout(() => {
                        btn.style.background = '';
                        btn.innerText = originalText;
                    }, 2000);
                }
            });
        });
    }
});