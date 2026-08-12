<?php

namespace App\Support;

class FhirIpsSummarizer
{
    private const ESTADOS_CLINICOS = [
        'active' => 'Activo',
        'recurrence' => 'Recurrente',
        'relapse' => 'Recaída',
        'inactive' => 'Inactivo',
        'remission' => 'En remisión',
        'resolved' => 'Resuelto',
    ];

    private const CRITICIDAD = [
        'high' => 'Alta',
        'low' => 'Baja',
        'unable-to-assess' => 'No evaluable',
    ];

    private const ESTADOS_MEDICACION = [
        'active' => 'Activo',
        'completed' => 'Completado',
        'entered-in-error' => 'Error',
        'intended' => 'Previsto',
        'stopped' => 'Suspendido',
        'on-hold' => 'En pausa',
        'unknown' => 'Desconocido',
        'not-taken' => 'No tomado',
        'cancelled' => 'Cancelado',
        'draft' => 'Borrador',
    ];

    private const ESTADOS_PROCEDIMIENTO = [
        'completed' => 'Completado',
        'in-progress' => 'En curso',
        'not-done' => 'No realizado',
        'on-hold' => 'En pausa',
        'stopped' => 'Suspendido',
        'entered-in-error' => 'Error',
        'unknown' => 'Desconocido',
    ];

    private const ESTADOS_ENCUENTRO = [
        'finished' => 'Finalizado',
        'in-progress' => 'En curso',
        'planned' => 'Planificado',
        'arrived' => 'Registrado',
        'cancelled' => 'Cancelado',
        'unknown' => 'Desconocido',
    ];

    private const CLASES_ENCUENTRO = [
        'AMB' => 'Ambulatorio',
        'IMP' => 'Hospitalización',
        'EMER' => 'Emergencia',
        'HH' => 'Atención domiciliaria',
        'VR' => 'Virtual',
    ];

    private const ESTADOS_OBSERVACION = [
        'final' => 'Final',
        'preliminary' => 'Preliminar',
        'amended' => 'Enmendado',
        'cancelled' => 'Cancelado',
        'entered-in-error' => 'Error',
        'unknown' => 'Desconocido',
    ];

    private const ESTADOS_INMUNIZACION = [
        'completed' => 'Completado',
        'entered-in-error' => 'Error',
        'not-done' => 'No realizado',
    ];

    private const GENEROS = [
        'male' => 'Masculino',
        'female' => 'Femenino',
        'other' => 'Otro',
        'unknown' => 'Desconocido',
    ];

    // Claves por código LOINC (fijo por la especificación IPS), no por el texto
    // libre del título de la sección, que varía entre documentos.
    private const SECCIONES_LOINC = [
        '11450-4' => ['titulo' => 'Problemas Activos', 'tipo' => 'problemas'],
        '10160-0' => ['titulo' => 'Medicación', 'tipo' => 'medicacion'],
        '48765-2' => ['titulo' => 'Alergias e Intolerancias', 'tipo' => 'alergias'],
        '47519-4' => ['titulo' => 'Procedimientos', 'tipo' => 'procedimientos'],
        '46240-8' => ['titulo' => 'Encuentros', 'tipo' => 'encuentros'],
        '30954-2' => ['titulo' => 'Resultados', 'tipo' => 'resultados'],
        '11369-6' => ['titulo' => 'Inmunizaciones', 'tipo' => 'inmunizaciones'],
    ];

    private const GRUPOS_SIN_COMPOSITION = [
        'Condition' => ['titulo' => 'Problemas Activos', 'tipo' => 'problemas'],
        'MedicationStatement' => ['titulo' => 'Medicación', 'tipo' => 'medicacion'],
        'MedicationRequest' => ['titulo' => 'Medicación', 'tipo' => 'medicacion'],
        'AllergyIntolerance' => ['titulo' => 'Alergias e Intolerancias', 'tipo' => 'alergias'],
        'Procedure' => ['titulo' => 'Procedimientos', 'tipo' => 'procedimientos'],
        'Encounter' => ['titulo' => 'Encuentros', 'tipo' => 'encuentros'],
        'Observation' => ['titulo' => 'Resultados', 'tipo' => 'resultados'],
        'Immunization' => ['titulo' => 'Inmunizaciones', 'tipo' => 'inmunizaciones'],
    ];

    public static function summarize(array $bundle): array
    {
        $map = [];
        foreach ($bundle['entry'] ?? [] as $entry) {
            if (isset($entry['fullUrl'], $entry['resource'])) {
                $map[$entry['fullUrl']] = $entry['resource'];
            }
        }

        $composition = self::findFirst($map, 'Composition');
        $paciente = self::findFirst($map, 'Patient');

        return [
            'titulo' => $composition['title'] ?? 'Resumen Clínico',
            'fecha' => $composition['date'] ?? null,
            'paciente' => $paciente ? self::paciente($paciente) : null,
            'autor' => self::autor($map, $composition),
            'organizacion' => self::organizacion($map, $composition),
            'secciones' => self::secciones($map, $composition),
        ];
    }

    private static function findFirst(array $map, string $resourceType): ?array
    {
        foreach ($map as $resource) {
            if (($resource['resourceType'] ?? null) === $resourceType) {
                return $resource;
            }
        }

        return null;
    }

    private static function resolveReference(array $map, ?string $reference): ?array
    {
        return $reference ? ($map[$reference] ?? null) : null;
    }

    private static function codeableConceptText(?array $concept): ?string
    {
        if (! $concept) {
            return null;
        }

        return $concept['coding'][0]['display']
            ?? $concept['text']
            ?? $concept['coding'][0]['code']
            ?? null;
    }

    private static function direccionTexto(array $address): string
    {
        $estructurada = trim(implode(' ', $address['line'] ?? []).', '.($address['city'] ?? '').' '.($address['country'] ?? ''), ' ,');

        return $estructurada !== '' ? $estructurada : ($address['text'] ?? '');
    }

    private static function paciente(array $patient): array
    {
        $name = $patient['name'][0] ?? [];
        $address = $patient['address'][0] ?? [];
        $telecom = collect($patient['telecom'] ?? [])->first(fn ($t) => ($t['system'] ?? null) === 'phone');

        return [
            'nombre' => $name['text'] ?? trim(($name['given'][0] ?? '').' '.($name['family'] ?? '')),
            'identificadores' => collect($patient['identifier'] ?? [])
                ->map(fn ($id) => [
                    'tipo' => $id['type']['coding'][0]['code'] ?? null,
                    'valor' => $id['value'] ?? null,
                ])
                ->filter(fn ($id) => $id['valor'])
                ->values()
                ->all(),
            'genero' => self::GENEROS[$patient['gender'] ?? ''] ?? ($patient['gender'] ?? null),
            'nacimiento' => $patient['birthDate'] ?? null,
            'direccion' => self::direccionTexto($address),
            'telefono' => $telecom['value'] ?? null,
            'activo' => $patient['active'] ?? null,
        ];
    }

    private static function autor(array $map, ?array $composition): ?array
    {
        $ref = $composition['author'][0]['reference'] ?? null;
        $resuelto = self::resolveReference($map, $ref);
        $practitioner = ($resuelto['resourceType'] ?? null) === 'Practitioner' ? $resuelto : self::findFirst($map, 'Practitioner');

        if (! $practitioner) {
            return null;
        }

        $name = $practitioner['name'][0] ?? [];

        return [
            'nombre' => trim(implode(' ', $name['given'] ?? []).' '.($name['family'] ?? '')),
            'identificador' => $practitioner['identifier'][0]['value'] ?? null,
        ];
    }

    private static function organizacion(array $map, ?array $composition): ?array
    {
        $ref = $composition['custodian']['reference'] ?? null;
        $org = self::resolveReference($map, $ref) ?? self::findFirst($map, 'Organization');

        if (! $org) {
            return null;
        }

        $address = $org['address'][0] ?? [];
        $telecom = $org['telecom'][0] ?? [];

        return [
            'nombre' => $org['name'] ?? null,
            'direccion' => self::direccionTexto($address),
            'telefono' => $telecom['value'] ?? null,
        ];
    }

    private static function secciones(array $map, ?array $composition): array
    {
        if ($composition && ! empty($composition['section'])) {
            return collect($composition['section'])
                ->map(function ($section) use ($map) {
                    $loinc = $section['code']['coding'][0]['code'] ?? null;
                    $info = self::SECCIONES_LOINC[$loinc] ?? ['titulo' => $section['title'] ?? 'Sección', 'tipo' => 'otro'];

                    return [
                        'titulo' => $info['titulo'],
                        'tipo' => $info['tipo'],
                        'items' => collect($section['entry'] ?? [])
                            ->map(fn ($entry) => self::resolveReference($map, $entry['reference'] ?? null))
                            ->map(fn (?array $resource) => self::item($resource))
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                })
                ->filter(fn ($section) => count($section['items']) > 0)
                ->values()
                ->all();
        }

        $grupos = [];
        foreach ($map as $resource) {
            $info = self::GRUPOS_SIN_COMPOSITION[$resource['resourceType'] ?? ''] ?? null;
            if ($info && ($resuelto = self::item($resource))) {
                $grupos[$info['tipo']] ??= ['titulo' => $info['titulo'], 'tipo' => $info['tipo'], 'items' => []];
                $grupos[$info['tipo']]['items'][] = $resuelto;
            }
        }

        return array_values($grupos);
    }

    private static function item(?array $resource): ?array
    {
        if (! $resource) {
            return null;
        }

        return match ($resource['resourceType'] ?? null) {
            'Condition' => self::itemCondition($resource),
            'MedicationStatement', 'MedicationRequest' => self::itemMedicacion($resource),
            'AllergyIntolerance' => self::itemAlergia($resource),
            'Procedure' => self::itemProcedimiento($resource),
            'Encounter' => self::itemEncuentro($resource),
            'Observation' => self::itemObservacion($resource),
            'Immunization' => self::itemInmunizacion($resource),
            default => null,
        };
    }

    private static function itemCondition(array $resource): array
    {
        $estado = $resource['clinicalStatus']['coding'][0]['code'] ?? null;
        $severidad = self::codeableConceptText($resource['severity'] ?? null);
        $inicio = $resource['onsetDateTime'] ?? $resource['onsetPeriod']['start'] ?? null;

        $detalles = array_filter([
            $severidad ? 'Severidad: '.$severidad : null,
            $inicio ? 'Inicio: '.$inicio : null,
        ]);

        return [
            'texto' => self::codeableConceptText($resource['code'] ?? null) ?? 'Condición sin descripción',
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_CLINICOS[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'active', 'recurrence', 'relapse' => 'warning',
                    'resolved' => 'success',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function itemMedicacion(array $resource): array
    {
        $medText = $resource['medicationReference']['display']
            ?? self::codeableConceptText($resource['medicationCodeableConcept'] ?? null)
            ?? 'Medicamento sin descripción';

        $estado = $resource['status'] ?? null;
        $dosage = $resource['dosage'][0] ?? $resource['dosageInstruction'][0] ?? [];
        $dosis = $dosage['doseAndRate'][0]['doseQuantity'] ?? null;
        $ruta = self::codeableConceptText($dosage['route'] ?? null);

        $inicio = $resource['effectivePeriod']['start'] ?? null;
        $fin = $resource['effectivePeriod']['end'] ?? ($inicio ? 'en curso' : null);

        $detalles = array_filter([
            $dosis ? 'Dosis: '.($dosis['value'] ?? '').' '.($dosis['unit'] ?? '') : null,
            $ruta ? 'Vía: '.$ruta : null,
            $inicio ? 'Desde: '.$inicio.($fin ? ' ('.$fin.')' : '') : null,
        ]);

        return [
            'texto' => $medText,
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_MEDICACION[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'active' => 'success',
                    'stopped', 'entered-in-error', 'not-taken', 'cancelled' => 'danger',
                    'on-hold', 'draft' => 'warning',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function itemEncuentro(array $resource): array
    {
        $tipo = self::codeableConceptText($resource['type'][0] ?? null)
            ?? self::CLASES_ENCUENTRO[$resource['class']['code'] ?? ''] ?? null
            ?? $resource['class']['code'] ?? null
            ?? 'Encuentro';

        $estado = $resource['status'] ?? null;
        $inicio = $resource['period']['start'] ?? null;
        $fin = $resource['period']['end'] ?? null;

        $detalles = array_filter([
            $inicio ? 'Inicio: '.$inicio : null,
            $fin ? 'Fin: '.$fin : null,
        ]);

        return [
            'texto' => $tipo,
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_ENCUENTRO[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'finished' => 'success',
                    'in-progress', 'arrived' => 'info',
                    'cancelled' => 'danger',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function itemObservacion(array $resource): array
    {
        $valor = self::valorObservacion($resource);
        $fecha = $resource['effectiveDateTime'] ?? $resource['effectivePeriod']['start'] ?? null;
        $estado = $resource['status'] ?? null;

        $detalles = array_filter([
            $valor ? 'Resultado: '.$valor : null,
            $fecha ? 'Fecha: '.$fecha : null,
        ]);

        return [
            'texto' => self::codeableConceptText($resource['code'] ?? null) ?? 'Observación sin descripción',
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_OBSERVACION[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'final' => 'success',
                    'preliminary', 'amended' => 'warning',
                    'cancelled', 'entered-in-error' => 'danger',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function valorObservacion(array $resource): ?string
    {
        if (isset($resource['valueQuantity'])) {
            return trim(($resource['valueQuantity']['value'] ?? '').' '.($resource['valueQuantity']['unit'] ?? ''));
        }
        if (isset($resource['valueString'])) {
            return $resource['valueString'];
        }
        if (isset($resource['valueCodeableConcept'])) {
            return self::codeableConceptText($resource['valueCodeableConcept']);
        }
        if (isset($resource['valueBoolean'])) {
            return $resource['valueBoolean'] ? 'Sí' : 'No';
        }

        return null;
    }

    private static function itemAlergia(array $resource): array
    {
        $criticidad = $resource['criticality'] ?? null;
        $categoria = implode(', ', $resource['category'] ?? []);
        $inicio = $resource['onsetDateTime'] ?? null;

        $detalles = array_filter([
            $categoria ? 'Categoría: '.$categoria : null,
            $inicio ? 'Inicio: '.$inicio : null,
        ]);

        return [
            'texto' => self::codeableConceptText($resource['code'] ?? null) ?? 'Alergia sin descripción',
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $criticidad ? [
                'texto' => self::CRITICIDAD[$criticidad] ?? $criticidad,
                'tono' => match ($criticidad) {
                    'high' => 'danger',
                    'low' => 'warning',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function itemInmunizacion(array $resource): array
    {
        $estado = $resource['status'] ?? null;
        $fecha = $resource['occurrenceDateTime'] ?? $resource['occurrenceString'] ?? null;
        $lote = $resource['lotNumber'] ?? null;

        $detalles = array_filter([
            $fecha ? 'Fecha: '.$fecha : null,
            $lote ? 'Lote: '.$lote : null,
        ]);

        return [
            'texto' => self::codeableConceptText($resource['vaccineCode'] ?? null) ?? 'Vacuna sin descripción',
            'detalle' => implode(' · ', $detalles),
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_INMUNIZACION[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'completed' => 'success',
                    'not-done', 'entered-in-error' => 'danger',
                    default => 'secondary',
                },
            ] : null,
        ];
    }

    private static function itemProcedimiento(array $resource): array
    {
        $fecha = $resource['performedDateTime'] ?? $resource['performedPeriod']['start'] ?? null;
        $estado = $resource['status'] ?? null;

        return [
            'texto' => self::codeableConceptText($resource['code'] ?? null) ?? 'Procedimiento sin descripción',
            'detalle' => $fecha ? 'Fecha: '.$fecha : '',
            'etiqueta' => $estado ? [
                'texto' => self::ESTADOS_PROCEDIMIENTO[$estado] ?? $estado,
                'tono' => match ($estado) {
                    'completed' => 'success',
                    'in-progress' => 'info',
                    'stopped', 'entered-in-error', 'not-done' => 'danger',
                    'on-hold' => 'warning',
                    default => 'secondary',
                },
            ] : null,
        ];
    }
}
