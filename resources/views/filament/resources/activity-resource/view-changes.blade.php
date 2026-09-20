@php
    $old = $activity->properties->get('old', []);
    $new = $activity->properties->get('attributes', []);
@endphp

<div class="space-y-6">
    <div>
        <p class="text-sm text-gray-500">
            Usuario
        </p>

        <p class="font-medium">
            {{ $activity->causer?->name ?? 'Sistema' }}
        </p>
    </div>

    <div>
        <p class="text-sm text-gray-500">
            Entidad
        </p>

        <p class="font-medium">
            {{ class_basename($activity->subject_type ?? '') }}
            #{{ $activity->subject_id }}
        </p>
    </div>

    <div>
        <p class="text-sm text-gray-500">
            Fecha
        </p>

        <p class="font-medium">
            {{ $activity->created_at?->format('d/m/Y H:i:s') }}
        </p>
    </div>

    @if (count($old) || count($new))
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-left">Campo</th>
                        <th class="py-2 text-left">Valor anterior</th>
                        <th class="py-2 text-left">Valor nuevo</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field)
                            <tr class="border-b">
                                <td class="py-2 font-medium">
                                    {{ $field }}
                                </td>

                                <td class="py-2">
                                    {{ is_array($old[$field] ?? null)
                        ? json_encode($old[$field])
                        : ($old[$field] ?? '—') }}
                                </td>

                                <td class="py-2">
                                    {{ is_array($new[$field] ?? null)
                        ? json_encode($new[$field])
                        : ($new[$field] ?? '—') }}
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500">
            Esta actividad no contiene cambios de atributos.
        </p>
    @endif
</div>