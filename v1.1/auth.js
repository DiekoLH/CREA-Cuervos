(function () {
  const USERS_KEY = "creaCuervosUsers";
  const SESSION_KEY = "creaCuervosSession";
  const DASHBOARDS = {
    alumno: "alumno.html",
    instructor: "instructor.html",
  };
  const LOGIN_PAGES = {
    alumno: "login_alumno.html",
    instructor: "login_docente.html",
  };

  function getUsers() {
    const storedUsers = localStorage.getItem(USERS_KEY);
    return storedUsers ? JSON.parse(storedUsers) : [];
  }

  function showMessage(form, message) {
    const messageElement = form.querySelector(".auth-message");
    messageElement.textContent = message;
    messageElement.hidden = false;
  }

  function normalized(value) {
    return value.trim().toLocaleLowerCase();
  }

  function getSession() {
    const storedSession = sessionStorage.getItem(SESSION_KEY);
    return storedSession ? JSON.parse(storedSession) : null;
  }

  const requiredRole = document.body.dataset.authRole;
  if (requiredRole) {
    let session;
    try {
      session = getSession();
    } catch (error) {
      alert("No se pudo leer la sesión. Inicia sesión nuevamente.");
      sessionStorage.removeItem(SESSION_KEY);
      window.location.replace(LOGIN_PAGES[requiredRole]);
      return;
    }

    if (!session || !DASHBOARDS[session.role]) {
      window.location.replace(LOGIN_PAGES[requiredRole]);
      return;
    }

    if (session.role !== requiredRole) {
      window.location.replace(DASHBOARDS[session.role]);
      return;
    }

    document.querySelectorAll("[data-auth-name]").forEach(function (element) {
      element.textContent = session.name;
    });
    document.querySelectorAll("[data-auth-email]").forEach(function (element) {
      element.textContent = session.email;
    });
    document
      .querySelectorAll("[data-auth-identifier]")
      .forEach(function (element) {
        element.textContent = session.identifier;
      });
    document
      .querySelectorAll("[data-auth-workshop]")
      .forEach(function (element) {
        element.textContent = session.workshop || "No especificado";
      });
    document.querySelectorAll("[data-auth-initials]").forEach(function (element) {
      element.textContent = session.name
        .split(/\s+/)
        .slice(0, 2)
        .map(function (part) {
          return part.charAt(0);
        })
        .join("")
        .toLocaleUpperCase();
    });
  }

  document.querySelectorAll("[data-logout]").forEach(function (link) {
    link.addEventListener("click", function (event) {
      event.preventDefault();
      sessionStorage.removeItem(SESSION_KEY);
      window.location.replace("indexMenu.html");
    });
  });

  document.querySelectorAll("[data-auth-form]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      const role = form.dataset.role;
      const formData = new FormData(form);
      const identifier = String(formData.get("identifier") || "").trim();
      const password = String(formData.get("password") || "");

      try {
        const users = getUsers();
        if (form.dataset.authForm === "login") {
          const user = users.find(function (account) {
            return (
              account.role === role &&
              (normalized(account.identifier) === normalized(identifier) ||
                normalized(account.email) === normalized(identifier)) &&
              account.password === password
            );
          });

          if (!user) {
            showMessage(
              form,
              "Usuario o contraseña incorrectos para este tipo de cuenta.",
            );
            return;
          }

          sessionStorage.setItem(
            SESSION_KEY,
            JSON.stringify({
              role: user.role,
              name: user.name,
              identifier: user.identifier,
              email: user.email,
              workshop: user.workshop,
            }),
          );
          window.location.assign(DASHBOARDS[role]);
          return;
        }

        const name = String(formData.get("name") || "").trim();
        const email = String(formData.get("email") || "").trim();
        const passwordConfirmation = String(
          formData.get("passwordConfirmation") || "",
        );
        const workshop = String(formData.get("workshop") || "");

        if (password !== passwordConfirmation) {
          showMessage(form, "Las contraseñas no coinciden.");
          return;
        }

        const accountExists = users.some(function (account) {
          return (
            normalized(account.identifier) === normalized(identifier) ||
            normalized(account.email) === normalized(email)
          );
        });
        if (accountExists) {
          showMessage(
            form,
            "La matrícula/identificación o el correo ya están registrados.",
          );
          return;
        }

        const user = {
          role: role,
          name: name,
          identifier: identifier,
          email: email,
          password: password,
          workshop: workshop,
        };
        users.push(user);
        localStorage.setItem(USERS_KEY, JSON.stringify(users));
        sessionStorage.setItem(
          SESSION_KEY,
          JSON.stringify({
            role: user.role,
            name: user.name,
            identifier: user.identifier,
            email: user.email,
            workshop: user.workshop,
          }),
        );
        window.location.assign(DASHBOARDS[role]);
      } catch (error) {
        showMessage(
          form,
          "No fue posible guardar o consultar las cuentas en este navegador.",
        );
      }
    });
  });
})();
