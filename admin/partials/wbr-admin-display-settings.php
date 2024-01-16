<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Wbr
 * @subpackage Wbr/admin/partials
 */

$client_id =        get_option('wbr-api-client-id');
$client_secret =    get_option('wbr-api-client-secret');
$access_token =     get_option('wbr-api-access-token');

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h2>Woober - Settings</h2>
    <p class="description">Uber API Access Settings</p>
    <form method="post" action="options.php">
        <?php
            settings_fields('woober_settings');
            do_settings_sections('woober_settings');
        ?>
        <div class="postbox">
            <div class="inside">
                <table class="form-table">
                    <tbody>
                        <tr class="mb-3">
                            <th>
                                <label class="form-label">Client ID</label>
                            </th>
                            <td>
                                <input type="text" name="wbr-api-client-id" value="<?php echo $client_id ?>" class="form-control" id="wbr-api-client-id">
                            </td>
                        </tr>
                        <tr class="mb-3">
                            <th>
                                <label class="form-label">Client Secret</label>
                            </th>
                            <td>
                                <input type="text" name="wbr-api-client-secret" value="<?php echo $client_secret ?>" class="form-control" id="wbr-api-client-secret">
                            </td>
                        </tr>
                        </tr>
                        <tr class="mb-3">
                            <th>
                                <label class="form-label">Access Token</label>
                            </th>
                            <td>
                                <input disabled type="text" name="wbr-api-access-token" value="<?php echo $access_token ?>" class="form-control" id="wbr-api-access-token">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php

                    $result = '';
                    $icon = '';

                    if ( $access_token == '' && ( $client_id && $client_secret != '' ) ) {
                        $result = 'Credenciais inválidas';
                        $icon = 'dashicons-no';
                    }
                ?>
                <button type="submit" class="button button-primary">Salvar</button>
                <?php 
                    if( $result != '' ) {
                        echo '<span class="dashicons ' . $icon . '"></span><span>'. $result . '</span>';
                    }
                ?>
            </div>
        </div>
    </form>
</div>