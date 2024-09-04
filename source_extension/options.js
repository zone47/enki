async function saveOptions(e) {
  e.preventDefault();
  await browser.storage.sync.set({
    lang: document.querySelector("#lang").value
  });
}

async function restoreOptions() {
  let res = await browser.storage.managed.get('lang');
  
  res = await browser.storage.sync.get('lang');
  document.querySelector("#lang").value = res.lang || 'fr';
}

document.addEventListener('DOMContentLoaded', restoreOptions);
document.querySelector("form").addEventListener("submit", saveOptions);