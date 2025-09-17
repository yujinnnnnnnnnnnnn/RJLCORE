document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a, .btn').forEach(el => {
    el.addEventListener('mouseover', () => el.classList.add('hovering'));
    el.addEventListener('mouseout', () => el.classList.remove('hovering'));
  });
  const links = document.querySelectorAll('a[href]');
  links.forEach(link => {
    link.addEventListener('click', (e) => {
      if (link.target === '_blank' || link.href.startsWith('#')) return;
      document.body.classList.add('page-fade-out');
    });
  });
});
