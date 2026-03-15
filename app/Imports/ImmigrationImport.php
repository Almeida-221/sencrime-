<?php

namespace App\Imports;

use App\Models\ImmigrationClandestine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class ImmigrationImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $importedCount = 0;
    public int $skippedCount  = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $date = null;
                if (!empty($row['date_interception'])) {
                    try {
                        $date = is_numeric($row['date_interception'])
                            ? Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_interception'])->format('Y-m-d'))
                            : Carbon::parse($row['date_interception']);
                    } catch (\Exception $e) {
                        $date = now();
                    }
                }

                ImmigrationClandestine::create([
                    'numero_cas'          => $row['numero_cas']          ?? 'IMM-' . now()->format('Ymd') . '-' . rand(100, 999),
                    'date_interception'   => $date ?? now(),
                    'localite'            => $row['localite']            ?? '',
                    'region'              => $row['region']              ?? auth()->user()->getRegionEffective() ?? '',
                    'lieu_interception'   => $row['lieu_interception']   ?? null,
                    'nombre_personnes'    => $row['nombre_personnes']    ?? 0,
                    'nombre_hommes'       => $row['nombre_hommes']       ?? 0,
                    'nombre_femmes'       => $row['nombre_femmes']       ?? 0,
                    'nombre_mineurs'      => $row['nombre_mineurs']      ?? 0,
                    'nationalites'        => $row['nationalites']        ?? null,
                    'pays_origine'        => $row['pays_origine']        ?? null,
                    'pays_destination'    => $row['pays_destination']    ?? null,
                    'moyen_transport'     => $row['moyen_transport']     ?? null,
                    'type_operation'      => in_array($row['type_operation'] ?? '', ['interception', 'arrestation', 'rapatriement'])
                                                ? $row['type_operation'] : 'interception',
                    'statut'              => 'ouvert',
                    'description'         => $row['description']        ?? null,
                    'user_id'             => auth()->id(),
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
            }
        }
    }
}
