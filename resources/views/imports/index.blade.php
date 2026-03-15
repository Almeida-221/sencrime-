@extends('layouts.app')

@section('title', 'Import Excel')

@section('content')
<div class="page-header mb-4">
    <h4 class="mb-1"><i class="fas fa-file-excel text-success me-2"></i>Import de données Excel</h4>
    <p class="text-muted mb-0">Importez vos données depuis des fichiers Excel (.xlsx, .xls) ou CSV</p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i>
    @foreach($errors->all() as $err) {{ $err }}<br> @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ── Accidents ── --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <div style="width:10px;height:10px;border-radius:50%;background:#e63946;"></div>
                <strong>Accidents</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Importez les données d'accidents de la circulation ou autres.</p>

                <a href="{{ route('imports.template.accidents') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                    <i class="fas fa-download me-1"></i> Télécharger le modèle CSV
                </a>

                <form action="{{ route('imports.accidents') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier Excel / CSV</label>
                        <input type="file" name="fichier" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-upload me-1"></i> Importer les accidents
                    </button>
                </form>

                <div class="mt-3 p-2 bg-light rounded small">
                    <strong>Colonnes attendues :</strong><br>
                    <code>numero_rapport, date_accident, localite, region, gravite (leger/grave/mortel), nombre_victimes, nombre_blesses, nombre_morts, description...</code>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Infractions ── --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <div style="width:10px;height:10px;border-radius:50%;background:#f4a261;"></div>
                <strong>Infractions</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Importez les données d'infractions et contraventions.</p>

                <a href="{{ route('imports.template.infractions') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                    <i class="fas fa-download me-1"></i> Télécharger le modèle CSV
                </a>

                <form action="{{ route('imports.infractions') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier Excel / CSV</label>
                        <input type="file" name="fichier" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm w-100">
                        <i class="fas fa-upload me-1"></i> Importer les infractions
                    </button>
                </form>

                <div class="mt-3 p-2 bg-light rounded small">
                    <strong>Colonnes attendues :</strong><br>
                    <code>numero_dossier, date_infraction, localite, region, type_infraction, nom_contrevenant, prenom_contrevenant, nationalite...</code>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Immigration ── --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <div style="width:10px;height:10px;border-radius:50%;background:#0077b6;"></div>
                <strong>Immigration clandestine</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Importez les cas d'immigration clandestine interceptés.</p>

                <a href="{{ route('imports.template.immigrations') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                    <i class="fas fa-download me-1"></i> Télécharger le modèle CSV
                </a>

                <form action="{{ route('imports.immigrations') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier Excel / CSV</label>
                        <input type="file" name="fichier" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-upload me-1"></i> Importer les cas
                    </button>
                </form>

                <div class="mt-3 p-2 bg-light rounded small">
                    <strong>Colonnes attendues :</strong><br>
                    <code>numero_cas, date_interception, localite, region, nombre_personnes, pays_origine, type_operation (interception/arrestation/rapatriement)...</code>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><i class="fas fa-info-circle me-2 text-info"></i>Instructions</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;">1</span>
                    <div><strong>Téléchargez le modèle</strong> CSV correspondant au type de données à importer.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;">2</span>
                    <div><strong>Remplissez le fichier</strong> en respectant les colonnes et les formats de dates (YYYY-MM-DD).</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;">3</span>
                    <div><strong>Uploadez le fichier</strong> via le formulaire. Les lignes invalides seront ignorées automatiquement.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
