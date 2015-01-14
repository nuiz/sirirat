<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 14/1/2558
 * Time: 11:07
 */

namespace Main\Http;


use Main\CTL\BaseCTL;

class ParseInput extends BaseCTL {
    public static function basic($input){
        return http_parse_params($input);
    }

    public static function json($input){
        return json_decode($input);
    }

    public static function multiPartFormData($input){
        $raw_data = $input;
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

// Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = array();
        $files = array();

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") break;

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, $type, $name) = $matches;
                $is_file = isset($matches[4]);
                isset($matches[4]) and $filename = $matches[4];

                // handle your fields here
                switch ($is_file) {
                    // this is a file upload
                    case true:
                        $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                        $temp_file = tempnam($tmp_dir, 'upl');
                        file_put_contents($temp_file, $body);
                        unset($body);
                        $files[$name] = [
                            'name'=> $filename,
                            'type'=> $headers['content-type'],
                            'tmp_name'=> $temp_file
                        ];
                        break;

                    // default for all other files is to populate $data
                    default:
                        $data[$name] = substr($body, 0, strlen($body) - 2);
                        break;
                }
            }
        }

        return ['data'=> $data, 'files'=> $files];
    }
}