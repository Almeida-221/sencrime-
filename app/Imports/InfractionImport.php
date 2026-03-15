<?php

namespace App\Imports;

use App\Models\Infraction;
use App\Models\TypeInfraction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class InfractionImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $importedCount = 0;
    public int $skippedCount  = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $date = null;
                if (!empty($row['date_infraction'])) {
                    try {
                        $date = is_numeric($row['date_infraction'])
                            ? Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_infraction'])->format('Y-m-d'))
                            : Carbon::parse($row['date_infraction']);
                    } catch (\Exception $e) {
                        $date = now();
                    }
                }

                // Chercher le type d'infraction par nom ou code
                $typeId = null;
                if (!empty($row['type_infraction'])) {
                    $type = TypeInfraction::where('nom', 'like', '%' . $row['type_infraction'] . '%')
                        ->orWhere('code', $row['type_infraction'])
                        ->first();
                    $typeId = $type?->id;
                }

                Infraction::create([
                    'numero_dossier'              => $row['numero_dossier']   ?? 'INF-' . now()->format('Ymd') . '-' . rand(100, 999),
                    'type_infraction_id'          => $typeId,
                    'date_infraction'             => $date ?? now(),
                    'localite'                    => $row['localite']         ?? '',
                    'region'                      => $row['region']           ?? auth()->user()->getRegionEffective() ?? '',
                    'description'                 => $row['description']      ?? null,
                    'nom_contrevenant'            => $row['nom_contrevenant'] ?? null,
                    'prenom_contrevenant'         => $row['prenom_contrevenant'] ?? null,
                    'nationalite_contrevenant'    => $row['nationalite']      ?? null,
                    'adresse_contrevenant'        => $row['adresse']          ?? null,
                    'statut'                      => 'ouvert',
                    'user_id'                     => auth()->id(),
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
            }
        }
    }
}
