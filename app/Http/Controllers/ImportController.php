<?php

namespace App\Http\Controllers;

use App\Imports\AccidentImport;
use App\Imports\InfractionImport;
use App\Imports\ImmigrationImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('imports.index');
    }

    public function accidents(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new AccidentImport();
        Excel::import($import, $request->file('fichier'));

        return back()->with('success', "Import terminé : {$import->importedCount} accident(s) importé(s), {$import->skippedCount} ignoré(s).");
    }

    public function infractions(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new InfractionImport();
        Excel::import($import, $request->file('fichier'));

        return back()->with('success', "Import terminé : {$import->importedCount} infraction(s) importée(s), {$import->skippedCount} ignorée(s).");
    }

    public function immigrations(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new ImmigrationImport();
        Excel::import($import, $request->file('fichier'));

        return back()->with('success', "Import terminé : {$import->importedCount} cas importé(s), {$import->skippedCount} ignoré(s).");
    }

    public function templateAccidents()
    {
        $headers = ['numero_rapport','date_accident','heure_accident','localite','region','lieu_exact','type_accident','description','nombre_victimes','nombre_blesses','nombre_morts','gravite','causes'];
        return $this->downloadTemplate('modele_accidents.csv', $headers);
    }

    public function templateInfractions()
    {
        $headers = ['numero_dossier','date_infraction','localite','region','type_infraction','description','nom_contrevenant','prenom_contrevenant','nationalite','adresse'];
        return $this->downloadTemplate('modele_infractions.csv', $headers);
    }

    public function templateImmigrations()
    {
        $headers = ['numero_cas','date_interception','localite','region','lieu_interception','nombre_personnes','nombre_hommes','nombre_femmes','nombre_mineurs','nationalites','pays_origine','pays_destination','moyen_transport','type_operation','description'];
        return $this->downloadTemplate('modele_immigrations.csv', $headers);
    }

    private function downloadTemplate(string $filename, array $headers): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, $headers, ';');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
