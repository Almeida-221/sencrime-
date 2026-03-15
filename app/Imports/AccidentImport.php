<?php

namespace App\Imports;

use App\Models\Accident;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class AccidentImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $importedCount = 0;
    public int $skippedCount  = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $date = null;
                if (!empty($row['date_accident'])) {
                    try {
                        $date = is_numeric($row['date_accident'])
                            ? Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_accident'])->format('Y-m-d'))
                            : Carbon::parse($row['date_accident']);
                    } catch (\Exception $e) {
                        $date = now();
                    }
                }

                Accident::create([
                    'numero_rapport'  => $row['numero_rapport']  ?? 'ACC-' . now()->format('Ymd') . '-' . rand(100, 999),
                    'date_accident'   => $date ?? now(),
                    'heure_accident'  => $row['heure_accident']  ?? null,
                    'localite'        => $row['localite']        ?? '',
                    'region'          => $row['region']          ?? auth()->user()->getRegionEffective() ?? '',
                    'lieu_exact'      => $row['lieu_exact']      ?? null,
                    'type_accident'   => $row['type_accident']   ?? null,
                    'description'     => $row['description']     ?? null,
                    'nombre_victimes' => $row['nombre_victimes'] ?? 0,
                    'nombre_blesses'  => $row['nombre_blesses']  ?? 0,
                    'nombre_morts'    => $row['nombre_morts']    ?? 0,
                    'gravite'         => in_array($row['gravite'] ?? '', ['leger', 'grave', 'mortel']) ? $row['gravite'] : 'leger',
                    'causes'          => $row['causes']          ?? null,
                    'statut'          => 'ouvert',
                    'user_id'         => auth()->id(),
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
            }
        }
    }
}
