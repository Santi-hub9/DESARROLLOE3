// Dashboard dinámico: consulta la API cada 10 s y actualiza KPIs, gráficas y Top 10
const charts = {};
const $ = id => document.getElementById(id);
function draw(id, config) {
  const cv = $(id); if (!cv) return;
  if (charts[id]) { charts[id].data = config.data; charts[id].update(); }
  else charts[id] = new Chart(cv, config);
}
async function cargar() {
  const r = await fetch(STATS_URL, { cache: 'no-store' });
  if (!r.ok) return;
  const d = await r.json(), k = d.kpis;
  const set = (id, v) => { if ($(id)) $(id).textContent = v; };
  set('kpi-total', k.total); set('kpi-prom', k.promedio); set('kpi-apro', k.aprobados); set('kpi-rech', k.rechazados); set('kpi-pend', k.pendientes);
  set('ult', new Date().toLocaleTimeString());

  draw('chDist', { type: 'bar', data: { labels: Object.keys(d.distribucion), datasets: [{ label: 'Candidatos', data: Object.values(d.distribucion),
    backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'] }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  draw('chEstado', { type: 'doughnut', data: { labels: ['Aprobados', 'Rechazados', 'En proceso'], datasets: [{ data: [k.aprobados, k.rechazados, k.pendientes], backgroundColor: ['#198754', '#6c757d', '#ffc107'] }] } });
  draw('chDia', { type: 'line', data: { labels: d.porDia.map(x => x.d.slice(5)), datasets: [{ label: 'Postulaciones', data: d.porDia.map(x => +x.n), borderColor: '#4f46e5', tension: .3, fill: true, backgroundColor: 'rgba(79,70,229,.1)' }] }, options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  draw('chPromDia', { type: 'line', data: { labels: d.promedioDia.map(x => x.d.slice(5)), datasets: [{ label: 'Promedio', data: d.promedioDia.map(x => +x.p), borderColor: '#0dcaf0', tension: .3 }] }, options: { scales: { y: { min: 0, max: 100 } } } });
  const tipos = ['preseleccion', 'agradecimiento'], est = ['enviado', 'fallido', 'pendiente'], col = { enviado: '#198754', fallido: '#dc3545', pendiente: '#ffc107' };
  draw('chCorreos', { type: 'bar', data: { labels: tipos, datasets: est.map(e => ({ label: e, backgroundColor: col[e],
    data: tipos.map(t => +(d.correos.find(c => c.tipo === t && c.estado_envio === e)?.n || 0)) })) }, options: { scales: { x: { stacked: true }, y: { stacked: true, ticks: { precision: 0 } } } } });

  const tb = $('top10');
  if (tb) tb.innerHTML = d.top10.length ? d.top10.map(r => {
    const c = r.puntuacion >= 80 ? 'success' : r.puntuacion >= 60 ? 'warning' : 'danger';
    return `<tr><td class="fw-bold">${r.posicion}</td><td>${esc(r.nombres + ' ' + r.apellidos)}</td><td>${esc(r.vacante)}</td>
      <td><div class="progress" style="height:18px"><div class="progress-bar bg-${c}" style="width:${r.puntuacion}%">${r.puntuacion}</div></div></td>
      <td><span class="badge text-bg-${r.estado === 'preseleccionado' ? 'success' : 'secondary'}">${r.estado.replace('_', ' ')}</span></td>
      <td><a class="btn btn-sm btn-outline-dark" href="${ANALISIS_URL}${r.analisis_id}">Detalle</a></td></tr>`;
  }).join('') : '<tr><td colspan="6" class="text-center text-muted">Sin datos todavía</td></tr>';
}
const esc = s => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
cargar(); setInterval(cargar, 10000);
