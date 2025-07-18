<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * LIBRERÍA CSV IMPORT
 * -------------------
 * Versión mejorada para el módulo Import Projects
 * 
 * @author Miguel Angel Sánchez
 * @version 1.2.0
 */
class Csvimport
{
    private $filepath;
    private $delimiter = ',';
    private $enclosure = '"';
    private $escape = '\\';
    private $max_line_length = 10000;
    private $skip_empty_lines = true;
    private $trim_fields = true;
    private $validate_headers = false;
    private $expected_headers = [];
    private $encoding = 'UTF-8';
    private $auto_detect_line_endings;
    private $header_mapping = [];
    private $column_count = 0;

    public function __construct($config = [])
    {
        $this->auto_detect_line_endings = (bool) ini_get('auto_detect_line_endings');
        $this->initialize($config);
    }

    /**
     * Inicializa la configuración
     */
    public function initialize($config = []): self
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    /**
     * Establece los headers esperados y mapeo opcional
     */
    public function set_expected_headers(array $headers, array $mapping = []): self
    {
        $this->expected_headers = $headers;
        $this->validate_headers = !empty($headers);
        $this->header_mapping = $mapping;
        return $this;
    }

    /**
     * Procesa el archivo CSV
     */
    public function get_array(string $filepath): array
    {
        $this->validate_file($filepath);
        $this->normalize_encoding();

        try {
            $handle = $this->open_file();
            $csv_data = $this->process_csv($handle);
            fclose($handle);
            return $csv_data;
        } finally {
            $this->restore_auto_detect();
        }
    }

    /**
     * Valida el archivo antes de procesarlo
     */
    private function validate_file(string $filepath): void
    {
        if (!file_exists($filepath)) {
            throw new RuntimeException("El archivo no existe: $filepath");
        }

        if (!is_readable($filepath)) {
            throw new RuntimeException("No se puede leer el archivo: $filepath");
        }

        $this->filepath = $filepath;
    }

    /**
     * Abre el archivo CSV
     */
    private function open_file()
    {
        $this->set_auto_detect(true);
        $handle = fopen($this->filepath, 'r');
        
        if ($handle === false) {
            throw new RuntimeException("Error al abrir el archivo: $this->filepath");
        }
        
        return $handle;
    }

    /**
     * Procesa el contenido CSV
     */
    private function process_csv($handle): array
    {
        $csv_data = [];
        $headers = [];
        $row = 0;

        while (($data = $this->read_csv_line($handle)) {
            if ($this->should_skip_line($data, $row)) {
                continue;
            }

            if ($row === 0) {
                $headers = $this->process_headers($data);
                $this->column_count = count($headers);
                $row++;
                continue;
            }

            $csv_data[] = $this->process_data_row($headers, $data, $row++);
        }

        return $csv_data;
    }

    /**
     * Lee una línea del CSV
     */
    private function read_csv_line($handle): ?array
    {
        return fgetcsv($handle, $this->max_line_length, $this->delimiter, $this->enclosure, $this->escape);
    }

    /**
     * Determina si se debe saltar una línea
     */
    private function should_skip_line(?array $data, int $row): bool
    {
        return ($this->skip_empty_lines && ($data === null || $data === [null])) || 
               ($row > 0 && $data === null);
    }

    /**
     * Procesa los encabezados del CSV
     */
    private function process_headers(array $data): array
    {
        $headers = $this->trim_fields ? array_map('trim', $data) : $data;

        if ($this->validate_headers) {
            $this->validate_headers($headers);
        }

        return $this->apply_header_mapping($headers);
    }

    /**
     * Valida los encabezados del CSV
     */
    private function validate_headers(array $headers): void
    {
        $missing = array_diff($this->expected_headers, $headers);
        
        if (!empty($missing)) {
            throw new RuntimeException(
                'Faltan encabezados requeridos: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Aplica mapeo de encabezados si está configurado
     */
    private function apply_header_mapping(array $headers): array
    {
        if (empty($this->header_mapping)) {
            return $headers;
        }

        return array_map(function ($header) {
            return $this->header_mapping[$header] ?? $header;
        }, $headers);
    }

    /**
     * Procesa una fila de datos
     */
    private function process_data_row(array $headers, array $data, int $row_number): array
    {
        $data = $this->normalize_data_length($data);
        
        return [
            '_row_number' => $row_number,
            '_raw_data' => $data,
            ...array_combine(
                $headers, 
                $this->trim_fields ? array_map('trim', $data) : $data
            )
        ];
    }

    /**
     * Normaliza la longitud de los datos
     */
    private function normalize_data_length(array $data): array
    {
        $count = count($data);
        
        if ($count < $this->column_count) {
            return array_pad($data, $this->column_count, null);
        }
        
        if ($count > $this->column_count) {
            return array_slice($data, 0, $this->column_count);
        }
        
        return $data;
    }

    /**
     * Normaliza la codificación del archivo
     */
    private function normalize_encoding(): void
    {
        if ($this->encoding === 'UTF-8') {
            return;
        }

        $content = file_get_contents($this->filepath);
        $content = mb_convert_encoding($content, 'UTF-8', $this->encoding);
        file_put_contents($this->filepath, $content);
    }

    /**
     * Configura auto_detect_line_endings
     */
    private function set_auto_detect(bool $value): void
    {
        if ($this->auto_detect_line_endings) {
            ini_set('auto_detect_line_endings', $value);
        }
    }

    /**
     * Restaura la configuración de auto_detect_line_endings
     */
    private function restore_auto_detect(): void
    {
        $this->set_auto_detect(false);
    }

    /**
     * Detecta el delimitador del archivo CSV
     */
    public static function detect_delimiter(string $filepath): string
    {
        $delimiters = [',', ';', "\t", '|', ':'];
        $best_delimiter = ',';
        $best_count = 0;

        $handle = fopen($filepath, 'r');
        $first_line = fgets($handle);
        fclose($handle);

        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($first_line, $delimiter));
            if ($count > $best_count) {
                $best_count = $count;
                $best_delimiter = $delimiter;
            }
        }

        return $best_delimiter;
    }

    /**
     * Obtiene información básica del archivo CSV
     */
    public function get_file_info(string $filepath): array
    {
        $this->validate_file($filepath);
        
        return [
            'size' => filesize($filepath),
            'modified' => filemtime($filepath),
            'delimiter' => self::detect_delimiter($filepath),
            'encoding' => mb_detect_encoding(file_get_contents($filepath))
        ];
    }
}