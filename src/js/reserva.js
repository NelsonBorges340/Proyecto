(function(){
  function el(tag, cls){ const e=document.createElement(tag); if(cls)e.className=cls; return e; }
  const fmt=d=>d.toISOString().slice(0,10);
  const toDate=s=>{const [Y,M,D]=s.split('-').map(Number);return new Date(Y,M-1,D);};
  const label=d=>d.toLocaleDateString('es-ES',{month:'long',year:'numeric'});

  // ---- Modal personalizado ----
  function confirmModal(mensaje){
    return new Promise(resolve=>{
      const overlay=document.getElementById('confirm-overlay');
      const text=document.getElementById('confirm-text');
      const ok=overlay.querySelector('.ok');
      const cancel=overlay.querySelector('.cancel');
      text.textContent=mensaje;
      overlay.style.display='flex';
      function close(v){overlay.style.display='none';ok.onclick=cancel.onclick=null;resolve(v);}
      ok.onclick=()=>close(true);
      cancel.onclick=()=>close(false);
    });
  }

  // ---- Dibuja el calendario ----
  function drawCalendar(container, servicioId, datos){
    container.innerHTML='';
    const msg=el('div','rz-msg'); container.appendChild(msg);
    const gridWrap=el('div','rz-months'); container.appendChild(gridWrap);
    const reserved=new Set((datos.reservados||[]));
    const start=toDate(datos.desde);
    const end=toDate(datos.hasta);
    const month2=new Date(start.getFullYear(), start.getMonth()+1, 1);

    [new Date(start.getFullYear(), start.getMonth(), 1), month2].forEach(firstDay=>{
      const monthBox=el('div','rz-month');
      const title=el('div','rz-title'); title.textContent=label(firstDay);
      const grid=el('div','rz-grid');
      monthBox.appendChild(title); monthBox.appendChild(grid);
      ['L','M','X','J','V','S','D'].forEach(d=>{const h=el('div','rz-dow');h.textContent=d;grid.appendChild(h);});
      const first=new Date(firstDay.getFullYear(), firstDay.getMonth(), 1);
      let w=first.getDay(); if(w===0)w=7; for(let i=1;i<w;i++){grid.appendChild(el('div','rz-empty'));}
      const last=new Date(firstDay.getFullYear(), firstDay.getMonth()+1, 0);

      for(let day=1;day<=last.getDate();day++){
        const cur=new Date(firstDay.getFullYear(), firstDay.getMonth(), day);
        if(cur<start||cur>end){grid.appendChild(el('div','rz-empty'));continue;}
        const ymd=fmt(cur);
        const btn=el('button','rz-day'); btn.type='button'; btn.textContent=String(day);

        if(reserved.has(ymd)){
          btn.classList.add('is-reserved'); btn.disabled=true;
        }else{
          btn.addEventListener('click', async ()=>{
            const ok=await confirmModal(`¿Confirmás la reserva para el ${ymd}?`);
            if(!ok) return;
            btn.disabled=true;
            const body=new URLSearchParams();
            body.set('servicio_id',String(servicioId));
            body.set('fecha',ymd);
            fetch('../php/reserva_crear.php',{
              method:'POST',
              headers:{'Content-Type':'application/x-www-form-urlencoded'},
              body
            }).then(r=>r.json()).then(j=>{
              if(j.ok){
                msg.textContent='✅ Reservado '+ymd;
                btn.classList.add('is-reserved'); reserved.add(ymd);
              }else{
                msg.textContent='⚠ '+(j.error||'No se pudo reservar');
                btn.disabled=false;
              }
            }).catch(()=>{
              msg.textContent='Error de red'; btn.disabled=false;
            });
          });
        }
        grid.appendChild(btn);
      }
      gridWrap.appendChild(monthBox);
    });
    msg.textContent='Elegí una fecha disponible';
  }

  function init(){
    const root=document.getElementById('reserva-root');
    if(!root||!root.dataset.servicioId)return;
    const servicioId=root.dataset.servicioId;
    fetch(`../php/reserva_get_calendario.php?servicio_id=${encodeURIComponent(servicioId)}`)
      .then(r=>r.json()).then(j=>{
        if(!j.ok)throw new Error(j.error||'Error');
        drawCalendar(root, servicioId, j);
      })
      .catch(()=>{root.innerHTML='<div class="rz-msg">No se pudo cargar el calendario</div>';});
  }

  document.addEventListener('DOMContentLoaded', init);
})();
