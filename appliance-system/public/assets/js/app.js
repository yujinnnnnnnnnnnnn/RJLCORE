document.addEventListener('DOMContentLoaded',()=>{
  const revealEls=[...document.querySelectorAll('.card,.fade-in')];
  const io=new IntersectionObserver((entries)=>{
    entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('reveal')}})
  },{threshold:.08});
  revealEls.forEach(el=>io.observe(el));
});

