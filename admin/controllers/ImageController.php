<?php

use Orhanerday\OpenAi\OpenAi;

class ImageController
{

    protected $api_key;

    private $resolution;
    private $variations_num;
    private $chosen_ai_model;
    private $text_to_image_quality;

    /** @var CgntvDnmcEncryption $encryption */
    private $encryption;

    public function __construct()
    {
        $this->encryption = CgntvDnmcEncryption::getInstance();
        $this->api_key = $this->encryption->decrypt(get_option('ai_wp_cgntvdnmc_api_key', ''));
        $this->resolution = get_option('ai_wp_cgntvdnmc_resolution', '');
        $this->variations_num = get_option('ai_wp_cgntvdnmc_variations_num', '');
        $this->chosen_ai_model = get_option('ai_wp_cgntvdnmc_chosen_ai_model', 'dall-e-2');
        $this->text_to_image_quality = get_option('ai_wp_cgntvdnmc_text_to_image_quality', 'standard');
    }

    public function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    public function validate_api_key($apiKey)
    {
        $open_ai = new OpenAi($apiKey);
        $response = $open_ai->listModels();

        return json_decode($response, true);
    }

    public function create_image_from_prompt()
    {
        check_ajax_referer('_wpnonce', 'security');
        if ( isset( $_POST['textToImagePrompt'] ) && !empty( $_POST['textToImagePrompt'] ) ) {
            $options = array(
                "prompt" => sanitize_textarea_field($_POST['textToImagePrompt']),
                "response_format" => 'b64_json',
            );
            if ( isset( $_POST['chosenAiModel'] ) && !empty( $_POST['chosenAiModel'] ) ) {
                $model = sanitize_text_field($_POST['chosenAiModel']);
                $options["model"] = $model;
               
                if ( $model == 'dall-e-3' ) {
                    $options["n"] = 1;

                    if ( isset( $_POST['textToImageResolution'] ) && !empty( $_POST['textToImageResolution'] ) ) {
                        $proRes = array('1792x1024', '1024x1792');

                        $sizeOption = sanitize_text_field($_POST['textToImageResolution']);
                        $options["size"] = $sizeOption;
                        if ( in_array($sizeOption, $proRes) && !aiwpcgntvdnmc_fs()->can_use_premium_code() ) {
                            return wp_send_json_error(__('Please upgrade to PRO to use this resolution.', 'ai-wp-cgntvdnmc'));
                        }
                    }
                    if ( isset( $_POST['textToImageQuality'] ) && !empty( $_POST['textToImageQuality'] ) ) {
                        $proQuality = array('hd');
                        $qualityOption = sanitize_text_field($_POST['textToImageQuality']);
                        $options["quality"] = $qualityOption;
                        if ( in_array($qualityOption, $proQuality) && !aiwpcgntvdnmc_fs()->can_use_premium_code() ) {
                            return wp_send_json_error(__('Please upgrade to PRO to use this quality.', 'ai-wp-cgntvdnmc'));
                        }
                    }
                    if ( isset( $_POST['textToImageStyle'] ) && !empty( $_POST['textToImageStyle'] ) ) {
                        $proStyle = array("natural");
                        $styleOption = sanitize_text_field($_POST['textToImageStyle']);
                        $options["style"] = $styleOption;
                        if ( in_array($styleOption, $proStyle) && !aiwpcgntvdnmc_fs()->can_use_premium_code() ) {
                            return wp_send_json_error(__('Please upgrade to PRO to use this style.', 'ai-wp-cgntvdnmc'));
                        }
                    }
                } else if ($model === 'dall-e-2') {
                    if ( isset( $_POST['textToImageResults'] ) && !empty( $_POST['textToImageResults'] ) ) {
                        $options["n"] = (int)sanitize_text_field($_POST['textToImageResults']);
                    }
                }
                // Create OpenAi instance and generate image
                $open_ai = new OpenAi($this->api_key);
                $response = $open_ai->image($options);

                return wp_send_json_success($response);
            }
            return wp_send_json_error(__('Please select a valid model.', 'ai-wp-cgntvdnmc'));
        }
        return wp_send_json_error(__('Prompt cannot be empty. Please type in a description of the image.', 'ai-wp-cgntvdnmc'));
    }


    public function generate_image_variations()
    {
        check_ajax_referer('_wpnonce', 'security');
        if (isset($_POST['image'])) {
            $image = sanitize_textarea_field($_POST['image']);
            $variationsRes = sanitize_text_field($_POST['variationsResolution']);
            $noOfVariations = (int)sanitize_text_field($_POST['noOfVariations']);

            $image_data = $this->save_base64_image($image);
            $resolution = isset($variationsRes) ? $variationsRes : $this->resolution;
            $variatiosNum = isset($noOfVariations) ? $noOfVariations : $this->variations_num;
            $cFile = new CURLFile($image_data['file_path'], $image_data['file_mime'], $image_data['file_name']);

            $open_ai = new OpenAi($this->api_key);
            $response = $open_ai->createImageVariation([
                "image" => $cFile,
                "n" => $variatiosNum,
                "size" => $resolution,
                "response_format" => 'b64_json',
            ]);
            unlink($image_data['file_path']);

            wp_send_json($response);
        }
        return wp_send_json_error( __('Complete all mandatory fields to generate images', 'ai-wp-cgntvdnmc') );
    }


    public function save_base64_image($base64_img)
    {
        // Get the upload directory and path
        $upload_dir = wp_upload_dir();
        $upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;
        // Remove the header of the base64 image string and replace any spaces with plus signs
        $img = str_replace('data:image/png;base64,', '', $base64_img);
        $img = str_replace(' ', '+', $img);

        // Compress the image
        $compressed_img = $this->compress_base64_image($img);

        // Decode the base64 image string and save it to a file
        $decoded = base64_decode($compressed_img);
        $filename = 'temporary-image-for-ai-37439' . '.png';
        $file_type = 'image/png';
        file_put_contents($upload_path . $filename, $decoded);

        // Return an array of the attachment details
        $attachment = array(
            'file_mime'      => $file_type,
            'file_name'      => $filename,
            'file_path'      => $upload_path . $filename,
        );

        return $attachment;
    }

    function compress_base64_image($base64_image)
    {
        $binary_data = base64_decode($base64_image);
        $max_filesize = 4 * 1024 * 1024;

        // If the binary data is already under the max file size, return the original image
        if (strlen($binary_data) <= $max_filesize) {
            return $base64_image;
        }

        $image = imagecreatefromstring($binary_data);
        $width = imagesx($image);
        $height = imagesy($image);
        $quality = 100;
        $min_quality = 85;

        do {
            // Decrease the quality level until the file size is within the max limit
            $compression_level = 9 - round($quality / 10);
            ob_start();
            imagepng($image, null, $compression_level);
            $binary_data = ob_get_clean();
            if (strlen($binary_data) > $max_filesize && $quality > $min_quality) {
                $quality -= 5;
            } elseif (strlen($binary_data) > $max_filesize) {
                // Decrease the image resolution until the file size is within the max limit
                $new_width = round($width * 0.1);
                $new_height = round($height * 0.1);
                $new_image = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagedestroy($image);
                $image = $new_image;
                $width = $new_width;
                $height = $new_height;
                $quality = 100;
            }
        } while (strlen($binary_data) > $max_filesize);

        imagedestroy($image);
        return base64_encode($binary_data);
    }

    function store_base64_image_in_media_library()
    {
        if (isset($_POST['base64Image'])) {
            $base64img = sanitize_textarea_field($_POST['base64Image']);
            $img = str_replace('data:image/png;base64,', '', $base64img);
            $img = str_replace(' ', '+', $img);

            // Get the binary data from the base64 encoded image string
            $binary_data = base64_decode($img);

            // Get the MIME type of the image
            $file_type = 'image/png';

            // Upload directory
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/';

            // Generate a unique file name for the image
            $filename = uniqid() . '.png';

            // Save the binary data as a file in the uploads directory
            $file_saved = file_put_contents($upload_path . $filename, $binary_data);

            // Check if the file was saved successfully
            if (!$file_saved) {
                // Return error message if the file was not saved successfully
                wp_send_json_error(array(
                    'success' => false,
                    'error' => 'Error saving the file'
                ));
            }

            // Add the file to the Media Library as an attachment
            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => $file_type,
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment, $upload_path . $filename);

            // Check if the attachment was added to the Media Library successfully
            if (!$attachment_id) {
                // Return error message if the attachment was not added successfully
                wp_send_json_error(array(
                    'success' => false,
                    'error' => 'Error adding the attachment to the Media Library'
                ));
            }

            // Generate metadata for the attachment
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload_path . $filename));

            // Return success message and attachment ID
            wp_send_json_success(array(
                'success' => true,
                'attachment_id' => $attachment_id
            ));
        }
    }
}
