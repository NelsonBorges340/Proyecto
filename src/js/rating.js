window.Rating = (function(){
  const api = {};
  function el(tag, cls){ const e = document.createElement(tag); if(cls) e.className = cls; return e; }

  function makeStars(score, opts={}){
    const readonly = !!opts.readonly;
    const wrap = el('span', readonly ? 'stars-inline' : 'stars-input');
    let current = Math.round(score||0);
    for(let i=1;i<=5;i++){
      const s = el('span','star ' + (i<=current?'filled':'empty') + (readonly?' readonly':''));
      s.dataset.value = i;
      if(!readonly){
        s.addEventListener('mouseenter',()=>paint(i));
        s.addEventListener('mouseleave',()=>paint(current));
        s.addEventListener('click',()=>{ current=i; paint(current); opts.onChange && opts.onChange(current); });
      }
      wrap.appendChild(s);
    }
    function paint(n){
      [...wrap.children].forEach((c,idx)=>{
        c.classList.toggle('filled', idx < n);
        c.classList.toggle('empty', idx >= n);
      });
    }
    paint(current);
    return {el:wrap, get:()=>current, set:(n)=>{current=n; paint(current);} };
  }

  api.upgradeAllStars = function(){
    document.querySelectorAll('.stars[data-score]').forEach(node=>{
      const score = parseFloat(node.dataset.score||'0')||0;
      const count = parseInt(node.dataset.count||'0')||0;
      const view = makeStars(Math.round(score), {readonly:true});
      node.innerHTML = '';
      node.appendChild(view.el);
      const span = el('span','rating-summary');
      span.innerHTML = `<span>${score.toFixed(1)}</span> <span class="count">(${count})</span>`;
      node.appendChild(span);
    });
  };

  function fetchJSON(url, opts){ return fetch(url, opts).then(r=>r.json()); }

  function renderRoot(root, servicioId){
    root.innerHTML = '';
    const header = el('div','rating-summary');
    const starsWrap = el('span','stars');
    const avgText = el('span'); avgText.style.marginLeft='6px';
    header.appendChild(starsWrap); header.appendChild(avgText);
    root.appendChild(header);

    const formWrap = el('div'); formWrap.style.marginTop='12px';
    const inputStars = makeStars(0, {onChange:(v)=>{submitBtn.disabled = (v<1);}});
    formWrap.appendChild(inputStars.el);
    const ta = el('textarea'); ta.placeholder="Dejá tu comentario (opcional)";
    formWrap.appendChild(ta);

    const actions = el('div','actions');
    const submitBtn = el('button'); submitBtn.textContent='Enviar calificación'; submitBtn.disabled=true;
    actions.appendChild(submitBtn);
    formWrap.appendChild(actions);
    root.appendChild(formWrap);

    const reviewsWrap = el('div'); reviewsWrap.style.marginTop='10px';
    root.appendChild(reviewsWrap);

    function load(){
      // ⬇⬇⬇ Ruta corregida (desde /html a /php hay que subir un nivel)
      fetchJSON(`../php/rating_get.php?servicio_id=${encodeURIComponent(servicioId)}`).then(data=>{
        if(!data.ok) throw new Error(data.error||'Error');
        const avg = data.promedio||0, cant = data.cantidad||0;
        const displayStars = makeStars(Math.round(avg), {readonly:true});
        starsWrap.innerHTML=''; starsWrap.appendChild(displayStars.el);
        avgText.textContent = `${avg.toFixed(1)} (${cant})`;

        reviewsWrap.innerHTML='';
        (data.reviews||[]).forEach(r=>{
          const item = el('div','review');
          const meta = el('div','meta');
          let starsTxt=''; for(let i=0;i<r.puntuacion;i++) starsTxt+='★'; for(let i=r.puntuacion;i<5;i++) starsTxt+='☆';
          meta.textContent = (r.cliente||'Anon') + ' • ' + starsTxt + ' • ' + r.fecha;
          const cmt = el('div','comment'); cmt.textContent = r.comentario||'';
          item.appendChild(meta); item.appendChild(cmt);
          reviewsWrap.appendChild(item);
        });
      }).catch(err=>{
        avgText.textContent = 'Error cargando calificaciones';
      });
    }

    submitBtn.addEventListener('click', ()=>{
      const body = new URLSearchParams();
      body.set('servicio_id', servicioId);
      body.set('puntuacion', String(inputStars.get()));
      if(ta.value.trim()) body.set('comentario', ta.value.trim());
      submitBtn.disabled = true;

      // ⬇⬇⬇ Ruta corregida (desde /html a /php hay que subir un nivel)
      fetchJSON('../php/rating_submit.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body
      }).then(data=>{
        if(data.ok){ ta.value=''; inputStars.set(0); load(); }
        else{ alert(data.error||'No se pudo enviar'); }
      }).catch(()=> alert('Error de red')).finally(()=> submitBtn.disabled=false);
    });

    load();
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    const root = document.getElementById('rating-root');
    if(root && root.dataset.servicioId){ renderRoot(root, root.dataset.servicioId); }
  });

  return api;
})();
