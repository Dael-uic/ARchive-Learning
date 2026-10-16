// Shared helpers used by every page.
// The API base is relative so this works whether the app is served at
// http://localhost:8000/app/ or deployed behind any domain later.
const API = "/api";

function getSession() {
  const raw = localStorage.getItem("ar3d_user");
  return raw ? JSON.parse(raw) : null;
}
function setSession(user) {
  localStorage.setItem("ar3d_user", JSON.stringify(user));
}
function clearSession() {
  localStorage.removeItem("ar3d_user");
}
function requireRole(role) {
  const u = getSession();
  if (!u || u.role !== role) {
    window.location.href = "login.html";
    return null;
  }
  return u;
}

async function apiGet(path) {
  const res = await fetch(API + path);
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}
async function apiPost(path, body) {
  const res = await fetch(API + path, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function renderNav(activePage) {
  const u = getSession();
  const el = document.getElementById("nav-links");
  if (!el) return;
  if (!u) {
    el.innerHTML = `<a href="login.html">Log in</a>`;
    return;
  }
  const links =
    u.role === "teacher"
      ? [["teacher.html", "Dashboard"], ["index.html", "Browse Models"]]
      : [["index.html", "Browse Models"], ["progress.html", "My Progress"]];
  el.innerHTML =
    links
      .map(
        ([href, label]) =>
          `<a href="${href}" class="${activePage === href ? "active" : ""}">${label}</a>`
      )
      .join("") + `<a href="#" id="logout-link">Log out (${u.name})</a>`;
  document.getElementById("logout-link").onclick = (e) => {
    e.preventDefault();
    clearSession();
    window.location.href = "login.html";
  };
}

// Register a service worker so previously-fetched 3D models stay viewable
// offline (FR6 — PWA caching strategy).
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("sw.js").catch(() => {});
  });
}
