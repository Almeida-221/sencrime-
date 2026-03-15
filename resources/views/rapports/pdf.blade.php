<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1a1a1a; background:#fff; }

    .page-header { background:linear-gradient(135deg,#1a3a5c,#2d6a9f); color:#fff; padding:20px 25px; margin-bottom:20px; }
    .page-header h1 { font-size:18px; font-weight:bold; margin-bottom:4px; }
    .page-header .sub { font-size:9px; opacity:.8; }
    .page-header .badges { margin-top:8px; }
    .badge { display:inline-block; background:rgba(255,255,255,.2); padding:3px 10px; border-radius:10px; font-size:8px; margin-right:6px; }

    .flag-stripe { height:4px; display:flex; width:100%; }
    .flag-stripe .g { background:#00853F; flex:1; }
    .flag-stripe .y { background:#FDEF42; flex:1; }
    .flag-stripe .r { background:#E31B23; flex:1; }

    .section-title { font-size:11px; font-weight:bold; color:#1a3a5c; border-bottom:2px solid #1a3a5c; padding-bottom:4px; margin-bottom:12px; margin-top:18px; text-transform:uppercase; letter-spacing:.5px; }

    .kpi-row { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .kpi { flex:1; min-width:80px; background:#f8f9fa; border:1px solid #e0e0e0; border-radius:6px; padding:10px 8px; text-align:center; }
    .kpi .val { font-size:20px; font-weight:bold; line-height:1; }
    .kpi .lbl { font-size:7.5px; color:#6c757d; text-transform:uppercase; letter-spacing:.3px; margin-top:3px; }
    .kpi.danger { border-color:#e63946; }
    .kpi.danger .val { color:#e63946; }
    .kpi.warning { border-color:#f4a261; }
    .kpi.warning .val { color:#f4a261; }
    .kpi.primary .val { color:#1a3a5c; }
    .kpi.blue .val { color:#0077b6; }

    .chart-section { margin-bottom:18px; }
    .chart-title { font-size:10px; font-weight:bold; color:#1a3a5c; margin-bottom:8px; }

    /* Barres SVG inline */
    .bar-chart { width:100%; }
    .bar-label { font-size:8px; fill:#333; }
    .bar-value { font-size:8px; fill:#fff; font-weight:bold; }

    table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    table th { background:#1a3a5c; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
    table td { padding:4px 8px; font-size:8px; border-bottom:1px solid #f0f0f0; }
    table tr:nth-child(even) td { background:#f8f9fa; }

    .row2 { display:flex; gap:12px; }
    .col-half { flex:1; }

    .footer { margin-top:20px; padding-top:10px; border-top:1px solid #e0e0e0; text-align:center; font-size:8px; color:#9ca3af; }

    .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- Bande drapeau --}}
<div class="flag-stripe"><div class="g"></div><div class="y"></div><div class="r"></div></div>

{{-- En-tête --}}
<div class="page-header">
    <table style="border:none;background:transparent;">
        <tr>
            <td style="width:60%;border:none;padding:0;">
                <h1>RAPPORT STATISTIQUE {{ $isAdmin ? 'NATIONAL' : 'RÉGIONAL' }}</h1>
                <div class="sub">République du Sénégal — Police Nationale — SenCrime</div>
                <div class="sub">Période : {{ $periodeLabel }}</div>
                <div class="sub">Généré le {{ now()->format('d/m/Y à H:i') }} par {{ $user->name }}</div>
                <div class="badges">
                    <span class="badge">&#128274; Confidentiel</span>
                    @if(!$isAdmin)<span class="badge">Région : {{ $region }}</span>@endif
                </div>
            </td>
            <td style="width:40%;text-align:right;border:none;padding:0;vertical-align:top;">
                <div style="font-size:32px;opacity:.3;">&#9737;</div>
                <div style="font-size:11px;font-weight:bold;opacity:.8;">SenCrime</div>
            </td>
        </tr>
    </table>
</div>

{{-- KPIs --}}
<div class="section-title">&#128200; Statistiques Générales</div>
<div class="kpi-row">
    <div class="kpi warning"><div class="val">{{ $stats['total_infractions'] }}</div><div class="lbl">Infractions</div></div>
    <div class="kpi danger"><div class="val">{{ $stats['total_accidents'] }}</div><div class="lbl">Accidents</div></div>
    <div class="kpi blue"><div class="val">{{ $stats['total_immigrations'] }}</div><div class="lbl">Cas Immigration</div></div>
    <div class="kpi primary"><div class="val">{{ $stats['total_migrants'] }}</div><div class="lbl">Migrants</div></div>
    <div class="kpi danger"><div class="val">{{ $stats['total_morts'] }}</div><div class="lbl">Décès</div></div>
    <div class="kpi warning"><div class="val">{{ $stats['total_blesses'] }}</div><div class="lbl">Blessés</div></div>
    <div class="kpi danger"><div class="val">{{ $stats['accidents_mortels'] }}</div><div class="lbl">Acc. Mortels</div></div>
    <div class="kpi"><div class="val" style="color:#2d6a4f;">{{ $stats['accidents_graves'] }}</div><div class="lbl">Acc. Graves</div></div>
</div>

{{-- Graphes barres SVG --}}
<div class="row2">
    {{-- Infractions par type --}}
    <div class="col-half">
        <div class="section-title">Infractions par type</div>
        @if($infractionsParType->count())
        @php
            $maxInf = $infractionsParType->max('total') ?: 1;
            $barColors = ['#1a3a5c','#e63946','#f4a261','#2d6a4f','#0077b6','#7b2d8b','#e9c46a','#264653'];
            $barH = 16; $gap = 4; $chartW = 220; $labelW = 80;
        @endphp
        <svg width="100%" viewBox="0 0 {{ $chartW + $labelW + 40 }} {{ $infractionsParType->count() * ($barH + $gap) + 5 }}">
            @foreach($infractionsParType as $i => $row)
            @php $bw = ($row->total / $maxInf) * $chartW; $y = $i * ($barH + $gap); @endphp
            <text x="{{ $labelW - 4 }}" y="{{ $y + $barH - 4 }}" text-anchor="end" style="font-size:8px;fill:#333;">{{ mb_substr($row->nom, 0, 14) }}</text>
            <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ max($bw,2) }}" height="{{ $barH }}" rx="3" fill="{{ $barColors[$i % count($barColors)] }}"/>
            <text x="{{ $labelW + $bw + 4 }}" y="{{ $y + $barH - 4 }}" style="font-size:8px;fill:#333;">{{ $row->total }}</text>
            @endforeach
        </svg>
        @else
        <p style="color:#9ca3af;font-size:9px;">Aucune donnée</p>
        @endif
    </div>

    {{-- Accidents par gravité (camembert SVG) --}}
    <div class="col-half">
        <div class="section-title">Accidents par gravité</div>
        @php
            $leger  = $accidentsParGravite['leger']  ?? 0;
            $grave  = $accidentsParGravite['grave']  ?? 0;
            $mortel = $accidentsParGravite['mortel'] ?? 0;
            $total  = $leger + $grave + $mortel ?: 1;

            function svgArc($cx,$cy,$r,$start,$end,$color,$label,$pct) {
                $s = deg2rad($start - 90); $e = deg2rad($end - 90);
                $x1 = $cx + $r*cos($s); $y1 = $cy + $r*sin($s);
                $x2 = $cx + $r*cos($e); $y2 = $cy + $r*sin($e);
                $large = ($end - $start) > 180 ? 1 : 0;
                $mid = deg2rad(($start+$end)/2 - 90);
                $lx = $cx + ($r+18)*cos($mid); $ly = $cy + ($r+18)*sin($mid);
                if ($pct < 3) return '';
                return "<path d='M {$cx} {$cy} L {$x1} {$y1} A {$r} {$r} 0 {$large} 1 {$x2} {$y2} Z' fill='{$color}'/>".
                    "<text x='{$lx}' y='{$ly}' text-anchor='middle' style='font-size:8px;fill:#333;'>{$label} ({$pct}%)</text>";
            }

            $p1 = round($leger/$total*100);
            $p2 = round($grave/$total*100);
            $p3 = 100 - $p1 - $p2;
            $a1 = $p1/100*360; $a2 = $p2/100*360; $a3 = 360 - $a1 - $a2;
        @endphp
        <svg width="100%" viewBox="0 0 200 130">
            {!! svgArc(80,65,50,0,$a1,'#40916c','Léger',$p1) !!}
            {!! svgArc(80,65,50,$a1,$a1+$a2,'#f4a261','Grave',$p2) !!}
            {!! svgArc(80,65,50,$a1+$a2,360,'#e63946','Mortel',$p3) !!}
            <text x="155" y="30" style="font-size:8px;fill:#333;">&#9632; Léger : {{ $leger }}</text>
            <text x="155" y="45" style="font-size:8px;fill:#333;">&#9632; Grave : {{ $grave }}</text>
            <text x="155" y="60" style="font-size:8px;fill:#333;">&#9632; Mortel: {{ $mortel }}</text>
        </svg>
    </div>
</div>

{{-- Immigration par pays --}}
@if($immigrationsParPays->count())
<div class="section-title">Immigration clandestine — Pays d'origine</div>
@php $maxImm = $immigrationsParPays->max('total_personnes') ?: 1; @endphp
<svg width="100%" viewBox="0 0 500 {{ $immigrationsParPays->count() * 20 + 5 }}">
    @foreach($immigrationsParPays as $i => $row)
    @php $bw = ($row->total_personnes / $maxImm) * 300; $y = $i * 20; @endphp
    <text x="85" y="{{ $y + 14 }}" text-anchor="end" style="font-size:8px;fill:#333;">{{ $row->pays_origine }}</text>
    <rect x="90" y="{{ $y + 2 }}" width="{{ max($bw,2) }}" height="14" rx="3" fill="#0077b6"/>
    <text x="{{ 92 + $bw }}" y="{{ $y + 14 }}" style="font-size:8px;fill:#333;">{{ $row->total_personnes }} pers. ({{ $row->total }} cas)</text>
    @endforeach
</svg>
@endif

{{-- Par région --}}
@if($isAdmin && isset($infractionsParRegion) && $infractionsParRegion->count())
<div class="page-break"></div>
<div class="flag-stripe"><div class="g"></div><div class="y"></div><div class="r"></div></div>
<br>
<div class="section-title">Analyse par région — Infractions</div>
<table>
    <thead><tr><th>Région</th><th>Infractions</th><th>Accidents</th><th>Tendance</th></tr></thead>
    <tbody>
    @foreach($infractionsParRegion as $r)
    @php
        $accR = $accidentsParRegion->firstWhere('region', $r->region);
        $maxR = $infractionsParRegion->max('total') ?: 1;
        $pct  = round($r->total / $maxR * 100);
    @endphp
    <tr>
        <td><strong>{{ $r->region }}</strong></td>
        <td>{{ $r->total }}</td>
        <td>{{ $accR->total ?? 0 }}</td>
        <td>
            <div style="background:#e0e0e0;border-radius:3px;height:8px;width:100px;display:inline-block;">
                <div style="background:#1a3a5c;border-radius:3px;height:8px;width:{{ $pct }}px;"></div>
            </div>
            <span style="font-size:7px;color:#6c757d;"> {{ $pct }}%</span>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- Top localités --}}
<div class="row2">
    @if($topLocalitesInfractions->count())
    <div class="col-half">
        <div class="section-title">Top localités — Infractions</div>
        <table>
            <thead><tr><th>#</th><th>Localité</th><th>Total</th></tr></thead>
            <tbody>
            @foreach($topLocalitesInfractions as $i => $l)
            <tr><td>{{ $i+1 }}</td><td>{{ $l->localite }}</td><td><strong>{{ $l->total }}</strong></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if($topLocalitesAccidents->count())
    <div class="col-half">
        <div class="section-title">Top localités — Accidents</div>
        <table>
            <thead><tr><th>#</th><th>Localité</th><th>Total</th></tr></thead>
            <tbody>
            @foreach($topLocalitesAccidents as $i => $l)
            <tr><td>{{ $i+1 }}</td><td>{{ $l->localite }}</td><td><strong>{{ $l->total }}</strong></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Évolution mensuelle --}}
@if(count($evolutionParMois['labels']) > 1)
<div class="section-title">Évolution mensuelle</div>
@php
    $maxEvo = max(max($evolutionParMois['infractions'] ?: [0]), max($evolutionParMois['accidents'] ?: [0]), max($evolutionParMois['migrations'] ?: [0]), 1);
    $evoW = 460; $evoH = 80; $nbMois = count($evolutionParMois['labels']); $colW = $evoW / max($nbMois,1);
@endphp
<svg width="100%" viewBox="0 0 {{ $evoW + 20 }} {{ $evoH + 30 }}">
    {{-- Lignes horizontales --}}
    @for($g=0;$g<=4;$g++)
    @php $gy = $evoH - ($g/4)*$evoH; @endphp
    <line x1="0" y1="{{ $gy }}" x2="{{ $evoW }}" y2="{{ $gy }}" stroke="#f0f0f0" stroke-width="1"/>
    @endfor

    {{-- Courbes --}}
    @foreach([['infractions','#f4a261'],['accidents','#e63946'],['migrations','#0077b6']] as [$key,$color])
    @php
        $pts = '';
        foreach($evolutionParMois[$key] as $idx => $v) {
            $px = $idx * $colW + $colW/2;
            $py = $evoH - ($v / $maxEvo) * $evoH;
            $pts .= "{$px},{$py} ";
        }
    @endphp
    <polyline points="{{ trim($pts) }}" fill="none" stroke="{{ $color }}" stroke-width="1.5"/>
    @foreach($evolutionParMois[$key] as $idx => $v)
    @php $px2 = $idx * $colW + $colW/2; $py2 = $evoH - ($v / $maxEvo) * $evoH; @endphp
    <circle cx="{{ $px2 }}" cy="{{ $py2 }}" r="3" fill="{{ $color }}"/>
    @endforeach
    @endforeach

    {{-- Labels axe X --}}
    @foreach($evolutionParMois['labels'] as $idx => $lbl)
    @php $px3 = $idx * $colW + $colW/2; @endphp
    <text x="{{ $px3 }}" y="{{ $evoH + 14 }}" text-anchor="middle" style="font-size:7px;fill:#6c757d;">{{ $lbl }}</text>
    @endforeach

    {{-- Légende --}}
    <circle cx="5"  cy="{{ $evoH + 26 }}" r="4" fill="#f4a261"/>
    <text x="12" y="{{ $evoH + 30 }}" style="font-size:7px;fill:#333;">Infractions</text>
    <circle cx="70" cy="{{ $evoH + 26 }}" r="4" fill="#e63946"/>
    <text x="77" y="{{ $evoH + 30 }}" style="font-size:7px;fill:#333;">Accidents</text>
    <circle cx="130" cy="{{ $evoH + 26 }}" r="4" fill="#0077b6"/>
    <text x="137" y="{{ $evoH + 30 }}" style="font-size:7px;fill:#333;">Immigration</text>
</svg>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAGE 2 — DÉTAIL PAR RÉGION (3 modules)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="flag-stripe"><div class="g"></div><div class="y"></div><div class="r"></div></div>
<br>

{{-- Variables partagées entre les 3 sections --}}
@php $chartWR = 260; $lblWR = 90; $barHR = 14; $gapR = 3; @endphp

{{-- ── INFRACTIONS PAR RÉGION ──────────────────────────────────── --}}
@if($infractionsParRegionDetail->count())
@php
    $totalInfNat = $infractionsParRegionDetail->sum('total') ?: 1;
    $maxInfR     = $infractionsParRegionDetail->max('total') ?: 1;
    $infColors   = ['#1a3a5c','#2d6a9f','#4a90d9','#0077b6','#023e8a','#48cae4','#0096c7','#00b4d8'];
@endphp
<div class="row2">
    <div class="col-half">
        <div class="section-title">&#128204; Infractions — Résumé par Région</div>
        <table>
            <thead>
                <tr>
                    <th>Région</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">% National</th>
                    <th>Proportion</th>
                </tr>
            </thead>
            <tbody>
            @foreach($infractionsParRegionDetail as $r)
            @php $pct = round($r->total / $totalInfNat * 100); @endphp
            <tr>
                <td><strong>{{ $r->region ?? '—' }}</strong></td>
                <td style="text-align:center;">{{ $r->total }}</td>
                <td style="text-align:center;">{{ $pct }}%</td>
                <td>
                    <div style="background:#e0e0e0;border-radius:3px;height:7px;width:80px;display:inline-block;vertical-align:middle;">
                        <div style="background:#1a3a5c;border-radius:3px;height:7px;width:{{ $pct }}px;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr style="background:#f0f4f8;">
                <td><strong>TOTAL</strong></td>
                <td style="text-align:center;"><strong>{{ $infractionsParRegionDetail->sum('total') }}</strong></td>
                <td style="text-align:center;"><strong>100%</strong></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-half">
        <div class="chart-title" style="margin-top:16px;">Infractions par région (barres)</div>
        <svg width="100%" viewBox="0 0 {{ $chartWR + $lblWR + 30 }} {{ $infractionsParRegionDetail->count() * ($barHR + $gapR) + 5 }}">
            @foreach($infractionsParRegionDetail as $i => $r)
            @php $bw = ($r->total / $maxInfR) * $chartWR; $y = $i * ($barHR + $gapR); @endphp
            <text x="{{ $lblWR - 4 }}" y="{{ $y + $barHR - 3 }}" text-anchor="end" style="font-size:7.5px;fill:#333;">{{ mb_substr($r->region ?? '—', 0, 14) }}</text>
            <rect x="{{ $lblWR }}" y="{{ $y }}" width="{{ max($bw, 2) }}" height="{{ $barHR }}" rx="2" fill="{{ $infColors[$i % count($infColors)] }}"/>
            <text x="{{ $lblWR + $bw + 4 }}" y="{{ $y + $barHR - 3 }}" style="font-size:7.5px;fill:#555;">{{ $r->total }}</text>
            @endforeach
        </svg>
    </div>
</div>

{{-- ── ACCIDENTS PAR RÉGION ─────────────────────────────────────── --}}
@endif
@if($accidentsParRegionDetail->count())
@php
    $totalAccNat = $accidentsParRegionDetail->sum('total') ?: 1;
    $maxAccR     = $accidentsParRegionDetail->max('total') ?: 1;
    $accRColors  = ['#e63946','#c1121f','#f4a261','#e9c46a','#d62828','#a4161a','#f48c06','#ffb703'];
@endphp
<div class="section-title" style="margin-top:16px;">&#128657; Accidents — Résumé par Région</div>
<div class="row2">
    <div class="col-half">
        <table>
            <thead>
                <tr>
                    <th>Région</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">Mortels</th>
                    <th style="text-align:center;">Graves</th>
                    <th style="text-align:center;">Légers</th>
                    <th style="text-align:center;">Décès</th>
                    <th style="text-align:center;">Blessés</th>
                </tr>
            </thead>
            <tbody>
            @foreach($accidentsParRegionDetail as $r)
            <tr>
                <td><strong>{{ $r->region ?? '—' }}</strong></td>
                <td style="text-align:center;">{{ $r->total }}</td>
                <td style="text-align:center;color:#e63946;font-weight:bold;">{{ $r->mortels ?? 0 }}</td>
                <td style="text-align:center;color:#f4a261;">{{ $r->graves ?? 0 }}</td>
                <td style="text-align:center;color:#40916c;">{{ $r->legers ?? 0 }}</td>
                <td style="text-align:center;color:#e63946;">{{ $r->morts ?? 0 }}</td>
                <td style="text-align:center;color:#f4a261;">{{ $r->blesses ?? 0 }}</td>
            </tr>
            @endforeach
            <tr style="background:#f0f4f8;">
                <td><strong>TOTAL</strong></td>
                <td style="text-align:center;"><strong>{{ $accidentsParRegionDetail->sum('total') }}</strong></td>
                <td style="text-align:center;color:#e63946;font-weight:bold;">{{ $accidentsParRegionDetail->sum('mortels') }}</td>
                <td style="text-align:center;color:#f4a261;">{{ $accidentsParRegionDetail->sum('graves') }}</td>
                <td style="text-align:center;color:#40916c;">{{ $accidentsParRegionDetail->sum('legers') }}</td>
                <td style="text-align:center;color:#e63946;">{{ $accidentsParRegionDetail->sum('morts') }}</td>
                <td style="text-align:center;color:#f4a261;">{{ $accidentsParRegionDetail->sum('blesses') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-half">
        <div class="chart-title" style="margin-top:4px;">Accidents par région — gravité</div>
        @php $maxStackW = 160; @endphp
        <svg width="100%" viewBox="0 0 {{ $maxStackW + $lblWR + 10 }} {{ $accidentsParRegionDetail->count() * ($barHR + $gapR + 2) + 30 }}">
            {{-- Légende --}}
            <rect x="0"  y="0" width="8" height="8" fill="#e63946"/><text x="11" y="8"  style="font-size:7px;fill:#333;">Mortel</text>
            <rect x="45" y="0" width="8" height="8" fill="#f4a261"/><text x="56" y="8"  style="font-size:7px;fill:#333;">Grave</text>
            <rect x="85" y="0" width="8" height="8" fill="#40916c"/><text x="96" y="8"  style="font-size:7px;fill:#333;">Léger</text>
            @foreach($accidentsParRegionDetail as $i => $r)
            @php
                $y = 16 + $i * ($barHR + $gapR + 2);
                $tot = max($r->total, 1);
                $wM = ($r->mortels / $tot) * $maxStackW;
                $wG = ($r->graves  / $tot) * $maxStackW;
                $wL = ($r->legers  / $tot) * $maxStackW;
            @endphp
            <text x="{{ $lblWR - 4 }}" y="{{ $y + $barHR - 3 }}" text-anchor="end" style="font-size:7.5px;fill:#333;">{{ mb_substr($r->region ?? '—', 0, 13) }}</text>
            <rect x="{{ $lblWR }}"           y="{{ $y }}" width="{{ max($wM,0) }}" height="{{ $barHR }}" fill="#e63946"/>
            <rect x="{{ $lblWR + $wM }}"     y="{{ $y }}" width="{{ max($wG,0) }}" height="{{ $barHR }}" fill="#f4a261"/>
            <rect x="{{ $lblWR+$wM+$wG }}"   y="{{ $y }}" width="{{ max($wL,0) }}" height="{{ $barHR }}" fill="#40916c"/>
            <text x="{{ $lblWR + $maxStackW + 4 }}" y="{{ $y + $barHR - 3 }}" style="font-size:7.5px;fill:#555;">{{ $r->total }}</text>
            @endforeach
        </svg>
    </div>
</div>

{{-- ── IMMIGRATION PAR RÉGION ───────────────────────────────────── --}}
@endif
@if($immigrationsParRegionDetail->count())
@php
    $totalImmNat = $immigrationsParRegionDetail->sum('personnes') ?: 1;
    $maxImmR     = $immigrationsParRegionDetail->max('personnes') ?: 1;
    $immColors   = ['#0077b6','#023e8a','#0096c7','#00b4d8','#48cae4','#90e0ef','#0077b6','#023e8a'];
@endphp
<div class="section-title" style="margin-top:16px;">&#128674; Immigration — Résumé par Région</div>
<div class="row2">
    <div class="col-half">
        <table>
            <thead>
                <tr>
                    <th>Région</th>
                    <th style="text-align:center;">Cas</th>
                    <th style="text-align:center;">Personnes</th>
                    <th style="text-align:center;">Hommes</th>
                    <th style="text-align:center;">Femmes</th>
                    <th style="text-align:center;">Mineurs</th>
                    <th style="text-align:center;">% Pers.</th>
                </tr>
            </thead>
            <tbody>
            @foreach($immigrationsParRegionDetail as $r)
            @php $pctImm = round(($r->personnes ?? 0) / $totalImmNat * 100); @endphp
            <tr>
                <td><strong>{{ $r->region ?? '—' }}</strong></td>
                <td style="text-align:center;">{{ $r->total }}</td>
                <td style="text-align:center;font-weight:bold;color:#0077b6;">{{ $r->personnes ?? 0 }}</td>
                <td style="text-align:center;">{{ $r->hommes ?? 0 }}</td>
                <td style="text-align:center;">{{ $r->femmes ?? 0 }}</td>
                <td style="text-align:center;color:#e63946;">{{ $r->mineurs ?? 0 }}</td>
                <td style="text-align:center;">{{ $pctImm }}%</td>
            </tr>
            @endforeach
            <tr style="background:#f0f4f8;">
                <td><strong>TOTAL</strong></td>
                <td style="text-align:center;"><strong>{{ $immigrationsParRegionDetail->sum('total') }}</strong></td>
                <td style="text-align:center;font-weight:bold;color:#0077b6;">{{ $immigrationsParRegionDetail->sum('personnes') }}</td>
                <td style="text-align:center;">{{ $immigrationsParRegionDetail->sum('hommes') }}</td>
                <td style="text-align:center;">{{ $immigrationsParRegionDetail->sum('femmes') }}</td>
                <td style="text-align:center;color:#e63946;">{{ $immigrationsParRegionDetail->sum('mineurs') }}</td>
                <td style="text-align:center;"><strong>100%</strong></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-half">
        <div class="chart-title" style="margin-top:4px;">Migrants par région (hommes / femmes / mineurs)</div>
        @php $maxStackImmW = 160; @endphp
        <svg width="100%" viewBox="0 0 {{ $maxStackImmW + $lblWR + 10 }} {{ $immigrationsParRegionDetail->count() * ($barHR + $gapR + 2) + 30 }}">
            <rect x="0"  y="0" width="8" height="8" fill="#0077b6"/><text x="11" y="8"  style="font-size:7px;fill:#333;">Hommes</text>
            <rect x="52" y="0" width="8" height="8" fill="#e63946"/><text x="63" y="8"  style="font-size:7px;fill:#333;">Femmes</text>
            <rect x="102" y="0" width="8" height="8" fill="#f4a261"/><text x="113" y="8" style="font-size:7px;fill:#333;">Mineurs</text>
            @foreach($immigrationsParRegionDetail as $i => $r)
            @php
                $y = 16 + $i * ($barHR + $gapR + 2);
                $tot = max($r->personnes ?? 1, 1);
                $wH = (($r->hommes   ?? 0) / $tot) * $maxStackImmW;
                $wF = (($r->femmes   ?? 0) / $tot) * $maxStackImmW;
                $wMi= (($r->mineurs  ?? 0) / $tot) * $maxStackImmW;
            @endphp
            <text x="{{ $lblWR - 4 }}" y="{{ $y + $barHR - 3 }}" text-anchor="end" style="font-size:7.5px;fill:#333;">{{ mb_substr($r->region ?? '—', 0, 13) }}</text>
            <rect x="{{ $lblWR }}"          y="{{ $y }}" width="{{ max($wH,0)  }}" height="{{ $barHR }}" fill="#0077b6"/>
            <rect x="{{ $lblWR + $wH }}"    y="{{ $y }}" width="{{ max($wF,0)  }}" height="{{ $barHR }}" fill="#e63946"/>
            <rect x="{{ $lblWR+$wH+$wF }}"  y="{{ $y }}" width="{{ max($wMi,0) }}" height="{{ $barHR }}" fill="#f4a261"/>
            <text x="{{ $lblWR + $maxStackImmW + 4 }}" y="{{ $y + $barHR - 3 }}" style="font-size:7.5px;fill:#555;">{{ $r->personnes ?? 0 }}</text>
            @endforeach
        </svg>
    </div>
</div>
@endif

{{-- Pied de page --}}
<div class="footer">
    <div class="flag-stripe" style="width:60px;display:inline-flex;margin-bottom:4px;border-radius:2px;overflow:hidden;">
        <div class="g" style="height:5px;"></div><div class="y" style="height:5px;"></div><div class="r" style="height:5px;"></div>
    </div><br>
    Système SenCrime — République du Sénégal — Document confidentiel — Accès réservé au personnel autorisé<br>
    Page générée automatiquement le {{ now()->format('d/m/Y à H:i') }}
</div>

</body>
</html>
