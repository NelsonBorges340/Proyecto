document.addEventListener("DOMContentLoaded", () => {
  const monthDisplay = document.querySelector(".month-display");
  const diasMes = document.querySelector(".dias-mes");
  const prev = document.querySelector("[data-nav='prev']");
  const next = document.querySelector("[data-nav='next']");

  const meses = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
  ];

  let fecha = new Date();

  const renderCalendario = () => {
    diasMes.innerHTML = "";
    const mes = fecha.getMonth();
    const año = fecha.getFullYear();
    const primerDia = new Date(año, mes, 1);
    const ultimoDia = new Date(año, mes + 1, 0);
    const primerSemana = primerDia.getDay() || 7;

    monthDisplay.textContent = `${meses[mes]} ${año}`;

 
    for (let i = 1; i < primerSemana; i++)
      diasMes.innerHTML += `<span class="vacio"></span>`;

    
    for (let d = 1; d <= ultimoDia.getDate(); d++) {
      const hoy = new Date();
      const clase = d === hoy.getDate() && mes === hoy.getMonth() && año === hoy.getFullYear() 
        ? "actual" : "dia";
      diasMes.innerHTML += `<span class="${clase}">${d}</span>`;
    }
  };

  prev.onclick = () => { fecha.setMonth(fecha.getMonth() - 1); renderCalendario(); };
  next.onclick = () => { fecha.setMonth(fecha.getMonth() + 1); renderCalendario(); };
  renderCalendario();
});
