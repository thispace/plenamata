<?php
/**
 * PlenamataPlugin Blocks
 *
 * @since   0.1.0
 * @link    hacklab.com.br
 * @license GPLv2 or later
 * @package PlenamataPlugin
 * @author  hacklab/
 */

namespace PlenamataPlugin\Admin;

use PlenamataPlugin\Plugin;

/**
 * Class SettingsPage
 *
 * @since   0.1.0
 *
 * @package PlenamataPlugin\Admin
 */
class Blocks {
    /**
	 * Init hooks
	 *
	 * @since 0.1.0
	 */
	public function hooks(): void {
        /**
         * Override the block core/latest-posts
         */
        remove_action( 'init', 'register_block_core_latest_posts', 10 );
        require_once PLENAMATA_PLUGIN_PATH . 'blocks/latest-posts.php';

		add_action( 'init', [ $this, 'register_blocks' ] );
	}

    public function filters(): void {
        add_filter( 'theme_page_templates', [ $this, 'custom_page_templates' ], 10, 1 );
    }

    public function custom_page_templates( array $templates ): array {
        $templates['template-dashboard.php'] = __( 'Dashboard', 'plenamata' );

        return $templates;
    }

    /**
	 * Register the JavaScript for the admin blocks.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
    public function register_blocks(): void {
        wp_register_script(
            'plenamata-plugin-blocks',
			PLENAMATA_PLUGIN_URL . 'assets/build/js/admin/blocks.js',
            [ 'wp-blocks', 'wp-i18n' ],
			Plugin::VERSION,
			false
		);

        wp_localize_script(
            'plenamata-plugin-blocks',
            'PlenamataBlocks',
            [
                'i18n' => [
                    '__' => [
                        'Contact information' => __( 'Contact information', 'plenamata' ),
                        'How to get involved' => __( 'How to get involved', 'plenamata' ),
                        'Project website' => __( 'Project website', 'plenamata' ),
                        'Social media' => __( 'Social media', 'plenamata' ),
                        'The initiative' => __( 'The initiative', 'plenamata' ),
                        'Upload media' => __( 'Upload media', 'plenamata' ),
                        'What it does' => __( 'What it does', 'plenamata' ),
                        'Where it operates' => __( 'Where it operates', 'plenamata' ),
                        'Who does it' => __( 'Who does it', 'plenamata' ),
                        'Who is it' => __( 'Who is it', 'plenamata' ),
                    ],
                ],
            ]
        );

        wp_register_style(
            'plenamata-plugin-blocks',
            PLENAMATA_PLUGIN_URL . 'assets/build/css/admin/blocks.css',
            [],
            Plugin::VERSION,
        );

        register_block_type( 'plenamata/verbete-subsection', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
        ] );

        register_block_type( 'plenamata/estimatives-area', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
            'script' => 'estimatives-area-front-end',
            'render_callback' => [ $this, 'estimatives_area_render_callback' ],
            'attributes' => [
                // Strings
                "headingTitle" => [
                    "type" => "string"
                ],
                "preNumberTitle" => [
                    "type" => "string"
                ],
                "averageTitle" => [
                    "type" => "string"
                ],
                "deforestedTitle" => [
                    "type" => "string"
                ],
                "finalInformation" => [
                    "type" => "string"
                ],
                // Base numbers
                "warnings" => [
                    "type" => "string"
                ],
            ],
        ] );

        register_block_type( 'plenamata/deforestation-info', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
            'script' => 'estimatives-area-front-end',
            'attributes' => [
                'boxTitle' => [
                    'type' => 'string'
                ],
            ],
        ] );

        register_block_type( 'plenamata/deforestation-charts', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
            'script' => 'deforestation-charts-front-end',
            'attributes' => [
                'boxTitle' => [
                    'type' => 'string'
                ],
            ],
        ] );

        register_block_type( 'plenamata/get-involved', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
            'attributes' => [
                'contactInfo' => [
                    'type' => 'string',
                ],
                'content' => [
                    'type' => 'string',
                ],
                'socialNetworks' => [
                    'type' => 'string',
                ],
                'website' => [
                    'type' => 'string',
                ],
            ],
        ] );

        register_block_type( 'plenamata/initiative', [
            'api_version' => 2,
            'editor_script' => 'plenamata-plugin-blocks',
            'editor_style' => 'plenamata-plugin-blocks',
            'attributes' => [
                'name' => [
                    'type' => 'string'
                ],
                'what' => [
                    'type' => 'string'
                ],
                'where' => [
                    'type' => 'string',
                ],
                'whereImage' => [
                    'type' => 'string',
                ],
                'who' => [
                    'type' => 'string'
                ],
            ]
        ]);
    }

    public function estimatives_area_render_callback( $attributes ) {

        extract( $attributes );

        ob_start(); ?>

            <div class="estimatives-area">
                <div class="heading">
                    <h3> <?= $headingTitle ?? '' ?> </h3>
                </div>

                <div class="main-data">
                    <h4><?= $preNumberTitle ?? '' ?></h4>

                    <div class="number">
                        <span data-deter="treesEstimative"><?= __('loading...', 'plenamata') ?></span>
                        <span><?= __("real-time estimate", "plenamata") ?></span>
                    </div>
                </div>

                <div class="base-data">
                    <div>
                        <div class="data">
                            <h4><?= $averageTitle ?? '' ?></h4>
                            <div class="area">
                                <span data-deter="treesPerDay"></span>
                                <span><?= __("trees", "plenamata") ?></span>
                            </div>

                            <div class="area">
                                <span data-deter="hectaresPerDay"></span>
                                <span><?= __("hectares", "plenamata") ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="data">
                            <h4><?= $deforestedTitle ?? '' ?></h4>

                            <div class="area">
                                <span data-mask="true"><?= $warnings ?? '' ?></span>
                                <span><?= __("alerts", "plenamata") ?></span>
                            </div>

                            <div class="area">
                                <span data-deter="hectaresThisYear"></span>
                                <span><?= __("hectares", "plenamata") ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="final-info">
                    <?= $finalInformation ?? '' ?>
                </div>
            </div>

        <?php
        $output = ob_get_clean();

        return $output;

    }
}