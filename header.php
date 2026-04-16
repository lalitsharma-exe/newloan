<?php
/**
 * The Header: Logo and main menu
 *
 * @package WordPress
 * @subpackage CUSTOM_MADE
 * @since CUSTOM_MADE 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js scheme_
<?php
// Class scheme_xxx need in the <html> as context for the <body>!
echo esc_attr(custom_made_get_theme_option('color_scheme'));
?>">

<head>
    <?php wp_head(); ?>

    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es',
                includedLanguages: 'en,de,es,ca',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false,
                multilanguagePage: true
            }, 'google_translate_element');

            // Add this line right below to force-hide the widget container
            document.getElementById('google_translate_element').style.display = 'none';
        }

        function triggerGoogleTranslate(langCode) {
            var select = document.querySelector('select.goog-te-combo');
            if (select) {
                select.value = langCode;
                select.dispatchEvent(new Event('change'));
            } else {
                // Updated to start from Spanish (es)
                window.location.hash = '#googtrans(es|' + langCode + ')';
                location.reload();
            }
        }
    </script>
    <script type="text/javascript"
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <script
        id="mcjs">!function (c, h, i, m, p) { m = c.createElement(h), p = c.getElementsByTagName(h)[0], m.async = 1, m.src = i, p.parentNode.insertBefore(m, p) }(document, "script", "https://chimpstatic.com/mcjs-connected/js/users/61385a87dee9b77e33a6cba1c/3f6fb2a6167418da1c0ce28b9.js");</script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17007717997"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'AW-17007717997');
    </script>


    <!-- Event snippet for Vista de página conversion page -->
    <script>
        gtag('event', 'conversion', { 'send_to': 'AW-17007717997/FJMNCMXm1rkaEO3c9K0_' });
    </script>

    <meta name="google-site-verification" content="oNnwne8eXt7pfGMP-wECnZIIHfPEwO-dGu4cIylTLqM" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V19QVQ7XJZ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-V19QVQ7XJZ');
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V19QVQ7XJZ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-V19QVQ7XJZ');
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9QHNR37CCD"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-9QHNR37CCD');
    </script>
    <meta name="msvalidate.01" content="ADF4BF2F00FE5F547C625CB6F9E1F934" />
    <style>
        /* Dark background overlay for the whole screen */
        .vacation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1 !important;
            visibility: visible !important;
            display: none;
        }

        /* Centered Card with Image Background */
        .vacation-popup-card {
            background-image: url('https://public.readdy.ai/ai/img_res/287abda55c0b0cd878484be963adbb55.jpg');
            background-size: cover;
            background-position: center;
            width: 90%;
            max-width: 550px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        /* Dark tint inside the card */
        .card-inner-overlay {
            background-color: rgba(0, 0, 0, 0.65);
            padding: 50px 40px;
        }

        /* Text styling for your complete lines */
        .popup-content-text {
            color: #ffffff !important;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 17px;
            line-height: 1.6;
            margin-bottom: 25px;
            font-weight: 500;
        }

        /* WhatsApp Button */
        .popup-wa-btn {
            background-color: #25D366 !important;
            color: white !important;
            text-decoration: none !important;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s ease;
            margin-top: 10px;
        }

        .popup-wa-btn:hover {
            transform: scale(1.05);
        }

        /* Close Button */
        .close-vacation {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 10;
        }

        /* Blue clickable number style */
        .whatsapp-link {
            color: #3498db !important;
            /* Professional Blue */
            text-decoration: underline !important;
            font-weight: bold;
        }

        .whatsapp-link:hover {
            color: #2980b9 !important;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="vacation-overlay" id="vacationPopup">
        <div class="vacation-popup-card">
            <span class="close-vacation"
                onclick="document.getElementById('vacationPopup').style.display='none'">&times;</span>

            <div class="card-inner-overlay">
                <div class="popup-content-text">
                    <p style="margin-bottom: 15px;">
                        Estaremos cerrados del 27 de marzo al 12 de abril por vacaciones, contacta por whatsapp al
                        <a href="https://wa.me/34618650171" target="_blank" class="whatsapp-link">+34 618 65 01 71</a>
                    </p>
                    <p style="margin-bottom: 15px;">
                        We will be closed from March 27th to April 12th for vacation. Please contact us via WhatsApp at
                        <a href="https://wa.me/34618650171" target="_blank" class="whatsapp-link">+34 618 65 01 71</a>
                    </p>
                    <!--<p style="margin-bottom: 10px;">Could be by the end of the weekend</p>-->
                    <p>Thanks</p>
                </div>

                <a href="https://wa.me/34618650171" target="_blank" class="popup-wa-btn">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.149-.174.198-.298.297-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>
    <div id="google_translate_element" style="display:none !important;"></div>

    <?php do_action('custom_made_action_before'); ?>

    <div class="body_wrap">
        <div class="page_wrap">

            <?php
            // Desktop header
            $custom_made_header_style = custom_made_get_theme_option("header_style");
            if (strpos($custom_made_header_style, 'header-custom-') === 0)
                $custom_made_header_style = 'header-custom';
            get_template_part("templates/{$custom_made_header_style}");

            // Side menu
            if (in_array(custom_made_get_theme_option('menu_style'), array('left', 'right'))) {
                get_template_part('templates/header-navi-side');
            }

            // Mobile header
            get_template_part('templates/header-mobile');
            ?>

            <div class="page_content_wrap scheme_<?php echo esc_attr(custom_made_get_theme_option('color_scheme')); ?>">

                <?php if (custom_made_get_theme_option('body_style') != 'fullscreen') { ?>
                    <div class="content_wrap">
                    <?php } ?>

                    <?php
                    // Widgets area above page content
                    custom_made_create_widgets_area('widgets_above_page');
                    ?>

                    <div class="content">
                        <?php
                        // Widgets area inside page content
                        custom_made_create_widgets_area('widgets_above_content');
                        ?>

                        <!-- Google tag (gtag.js) -->
                        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17007717997">
                        </script>
                        <script>
                            window.dataLayer = window.dataLayer || [];
                            function gtag() { dataLayer.push(arguments); }
                            gtag('js', new Date());

                            gtag('config', 'AW-17007717997');
                        </script>