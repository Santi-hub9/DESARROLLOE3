<div class="hero text-white rounded-4 p-5 mb-4 text-center">
 <h1 class="display-5 fw-bold">TALENTIA</h1>
 <p class="lead mb-4">Sistema inteligente de reclutamiento y selección de personal. Sube tu hoja de vida y nuestra IA la evalúa en segundos.</p>
 <a href="<?= url('candidato') ?>" class="btn btn-light btn-lg"><i class="bi bi-upload"></i> Postularme ahora</a>
</div>
<div class="row g-4 text-center">
 <?php foreach ([['bi-cloud-arrow-up','1. Registra y sube tu CV','Formulario sencillo y PDF seguro.'],['bi-cpu','2. La IA analiza','Experiencia, habilidades, formación, certificaciones e idiomas.'],['bi-envelope-check','3. Recibe respuesta','Correo personalizado automático según tu puntaje.']] as [$i,$t,$d]): ?>
 <div class="col-md-4"><div class="card h-100 shadow-sm border-0"><div class="card-body"><i class="bi <?= $i ?> fs-1 text-primary"></i><h5 class="mt-2"><?= $t ?></h5><p class="text-muted"><?= $d ?></p></div></div></div>
 <?php endforeach; ?>
</div>
