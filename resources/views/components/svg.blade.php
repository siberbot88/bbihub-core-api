@props(['name', 'class' => ''])

@php
    // lokasi folder svg kamu
    $path = resource_path("views/components/icons/{$name}.svg");

    if (file_exists($path)) {
        $svg = file_get_contents($path);

        // Tambahkan class ke <svg> utama
        if ($class) {
            $svg = preg_replace(
                '/<svg(?![^>]*\bclass=)([^>]*)>/i',
                '<svg class="'.$class.'"$1>',
                $svg,
                1
            );
        }

        echo $svg;
    } else {
        echo '<!-- svg '.$name.' not found -->';
    }
@endphp
