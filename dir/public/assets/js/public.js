document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', e => {
        const link = e.target.closest('a');
        if (link && link.origin === window.location.origin && link.target !== "_blank" && !link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            navigate(link.href);
        }
    });

    window.addEventListener('popstate', () => {
        navigate(window.location.href, false);
    });

    initPage();
});

async function navigate(url, pushState = true) {
    const appContent = document.getElementById('app-content');
    if (!appContent) return;

    appContent.classList.add('fade-out');

    try {
        const response = await fetch(url);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newContent = doc.getElementById('app-content').innerHTML;
        document.title = doc.title;

        setTimeout(() => {
            appContent.innerHTML = newContent;
            appContent.classList.remove('fade-out');
            if (pushState) window.history.pushState({}, '', url);


            initPage();
        }, 300);
    } catch (error) {
        window.location.href = url;
    }
}


function updateScrollIndicator() {
    const formsContainer = document.querySelector('.forms-container');
    const scrollIndicator = document.getElementById('scrollIndicator');
    if (formsContainer && scrollIndicator) {
        const maxScroll = formsContainer.scrollHeight - formsContainer.clientHeight;
        if (maxScroll > 0) {
            const scrollPercent = (formsContainer.scrollTop / maxScroll) * 100;
            scrollIndicator.style.height = `${scrollPercent}%`;
        } else {
            scrollIndicator.style.height = '0%';
        }
    }
}

function initPage() {

    const tabBtns = document.querySelectorAll('.tab-btn');
    const formPanes = document.querySelectorAll('.form-pane');

    tabBtns.forEach(btn => {

        btn.onclick = (e) => {
            e.preventDefault();
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            formPanes.forEach(pane => pane.classList.remove('active'));
            
            const targetId = btn.dataset.target === 'register' ? 'registerForm' : 'loginForm';
            const targetForm = document.getElementById(targetId);
            if (targetForm) targetForm.classList.add('active');
            

            setTimeout(updateScrollIndicator, 100);
        };
    });


    const rolSelect = document.getElementById('rolSelect');
    const dynamicFields = document.getElementById('dynamicFields');

    if (rolSelect && dynamicFields) {
        rolSelect.onchange = function() {
            let html = '';
            const rol = this.value;
            if (rol === 'director') {
                html = `<div class="field dynamic-field" style="animation-delay: 0.1s;"><input type="text" name="universidad" placeholder="Nombre de la Universidad" required></div>
                        <div class="field dynamic-field" style="animation-delay: 0.2s;"><input type="text" name="carrera" placeholder="Nombre de la Carrera" required></div>
                        <div class="field dynamic-field" style="animation-delay: 0.3s;"><input type="text" name="telefono" placeholder="Teléfono de contacto"></div>`;
            } else if (rol === 'encargado') {
                html = `<div class="field dynamic-field" style="animation-delay: 0.1s;"><input type="text" name="telefono" placeholder="Teléfono de contacto" required></div>
                        <div class="field dynamic-field" style="animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Director" required></div>`;
            } else if (rol === 'estudiante') {
                html = `<div class="field dynamic-field" style="animation-delay: 0.1s;"><input type="number" name="rut" placeholder="RUT (Sin guion)" required></div>
                        <div class="field dynamic-field" style="animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Encargado" required></div>`;
            } else if (rol === 'tutor') {
                html = `<div class="field dynamic-field" style="animation-delay: 0.1s;"><input type="text" name="empresa" placeholder="Empresa donde trabajas" required></div>
                        <div class="field dynamic-field" style="animation-delay: 0.2s;"><input type="text" name="cargo" placeholder="Tu cargo"></div>
                        <div class="field dynamic-field" style="animation-delay: 0.3s;"><input type="text" name="telefono" placeholder="Teléfono"></div>`;
            }
            dynamicFields.innerHTML = html;
            

            setTimeout(updateScrollIndicator, 50);
        };
    }


    const handleFormSubmit = (formId, loadingText) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.onsubmit = function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-primary');
            const originalText = btn.innerText;
            btn.innerText = loadingText;
            
            fetch(this.action, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    if (formId === 'registerForm') {
                        alert("Registro exitoso. " + (res.codigo ? "Código: " + res.codigo : ""));
                        document.querySelector('[data-target="login"]').click();
                    } else {

                        navigate('inicio.php');
                    }
                } else {
                    btn.style.background = '#e57373';
                    btn.innerText = res.message || 'Error';
                    setTimeout(() => { btn.style.background = ''; btn.innerText = originalText; }, 2000);
                }
            });
        };
    };
    handleFormSubmit('loginForm', 'Cargando...');
    handleFormSubmit('registerForm', 'Registrando...');


    const formsContainer = document.querySelector('.forms-container');
    if (formsContainer) {
        formsContainer.addEventListener('scroll', updateScrollIndicator);
    }


    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('mode') === 'register') {
        const registerTab = document.querySelector('[data-target="register"]');
        if (registerTab) registerTab.click();
    }

    setTimeout(updateScrollIndicator, 200);
}