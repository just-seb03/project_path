document.addEventListener("DOMContentLoaded", () => {
  document.body.addEventListener("click", (e) => {
    const link = e.target.closest("a");
    if (
      link &&
      link.origin === window.location.origin &&
      link.target !== "_blank" &&
      !link.getAttribute("href").startsWith("#")
    ) {
      e.preventDefault();
      navigate(link.href);
    }
  });

  window.addEventListener("popstate", () => {
    navigate(window.location.href, false);
  });

  initPage();
});

async function navigate(url, pushState = true) {
  const appContent = document.getElementById("app-content");
  if (!appContent) return;

  appContent.classList.add("fade-out");

  try {
    const response = await fetch(url);
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");

    const newContent = doc.getElementById("app-content").innerHTML;
    document.title = doc.title;

    setTimeout(() => {
      appContent.innerHTML = newContent;
      appContent.classList.remove("fade-out");
      if (pushState) window.history.pushState({}, "", url);

      initPage();
    }, 300);
  } catch (error) {
    window.location.href = url;
  }
}

function updateScrollIndicator() {
  const formsContainer = document.querySelector(".forms-container");
  const scrollIndicator = document.getElementById("scrollIndicator");
  if (formsContainer && scrollIndicator) {
    const maxScroll = formsContainer.scrollHeight - formsContainer.clientHeight;
    if (maxScroll > 0) {
      const scrollPercent = (formsContainer.scrollTop / maxScroll) * 100;
      scrollIndicator.style.height = `${scrollPercent}%`;
    } else {
      scrollIndicator.style.height = "0%";
    }
  }
}

function initPage() {
  const tabBtns = document.querySelectorAll(".tab-btn");
  const formPanes = document.querySelectorAll(".form-pane");

  tabBtns.forEach((btn) => {
    btn.onclick = (e) => {
      e.preventDefault();
      tabBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      formPanes.forEach((pane) => pane.classList.remove("active"));

      const targetId =
        btn.dataset.target === "register" ? "registerForm" : "loginForm";
      const targetForm = document.getElementById(targetId);
      if (targetForm) targetForm.classList.add("active");

      setTimeout(updateScrollIndicator, 100);
    };
  });

  const handleFormSubmit = (formId, loadingText) => {
    const form = document.getElementById(formId);
    if (!form) return;
    form.onsubmit = function (e) {
      e.preventDefault();
      const btn = this.querySelector(".btn-primary");
      const originalText = btn.innerText;
      btn.innerText = loadingText;

      fetch(this.getAttribute("action"), {
        method: "POST",
        body: new FormData(this),
      })
        .then((res) => res.json())
        .then((res) => {
          if (res.status === "success") {
            if (formId === "registerForm") {
              alert(
                "Registro exitoso. " +
                  (res.codigo ? "Código: " + res.codigo : ""),
              );
              document.querySelector('[data-target="login"]').click();
            } else {
              navigate("success.php");
            }
          } else {
            btn.style.background = "#e57373";
            btn.innerText = res.message || "Error";
            setTimeout(() => {
              btn.style.background = "";
              btn.innerText = originalText;
            }, 2000);
          }
        });
    };
  };

  handleFormSubmit("loginForm", "Cargando...");
  handleFormSubmit("registerForm", "Registrando...");

  const formsContainer = document.querySelector(".forms-container");
  if (formsContainer) {
    formsContainer.addEventListener("scroll", updateScrollIndicator);
  }

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("mode") === "register") {
    const registerTab = document.querySelector('[data-target="register"]');
    if (registerTab) registerTab.click();
  }

  setTimeout(updateScrollIndicator, 200);

  const commitsContainer = document.getElementById("commits-container");
  if (commitsContainer) {
    fetch("https://api.github.com/repos/just-seb03/project_path/commits")
      .then((response) => response.json())
      .then((data) => {
        commitsContainer.innerHTML = "";
        if (Array.isArray(data)) {
          data.forEach((commitObj, index) => {
            const commit = commitObj.commit;
            const author = commit.author.name;
            const message = commit.message;
            const date = new Date(commit.author.date).toLocaleString("es-CL");
            const url = commitObj.html_url;
            const sha = commitObj.sha.substring(0, 7);

            const card = document.createElement("div");
            card.className = "commit-card";
            card.style.animationDelay = `${(index * 0.1).toFixed(1)}s`;

            card.innerHTML = `
                            <div class="commit-header">
                                <span class="commit-author">${author}</span>
                                <span class="commit-date">${date}</span>
                            </div>
                            <div class="commit-message">${message}</div>
                            <a href="${url}" target="_blank" class="commit-hash">${sha}</a>
                        `;
            commitsContainer.appendChild(card);
          });
        } else {
          commitsContainer.innerHTML =
            '<div class="glass-card-info"><p>Error al cargar los commits.</p></div>';
        }
      })
      .catch((error) => {
        commitsContainer.innerHTML =
          '<div class="glass-card-info"><p>Error de conexión con GitHub.</p></div>';
      });
  }
}
