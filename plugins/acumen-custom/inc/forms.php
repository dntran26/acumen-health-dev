<?php
/**
 * Form helpers for Acumen Custom plugin.
 */

/**
 * Output JS to handle province to city dropdown options on the contact page.
 */
function acumen_form_province_city_dropdown_script() {
    // Only run on the contact page.
    // Adjust the slug or use is_page(123) if needed.
    if ( ! is_page( 'contact-us' ) ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Update these IDs to match your Gravity Form.
      var provinceSelect = document.querySelector('#input_1_8'); // Province
      var citySelect     = document.querySelector('#input_1_9'); // City

      if (!provinceSelect || !citySelect) return;

      // Province to city map.
      var cityOptions = {
        'Alberta': [
          'Edmonton',
          'Calgary'
        ],
        'British Columbia': [
          'Vancouver',
          'Langley',
          'Kelowna'
        ]
      };

      function populateCities() {
        var province = provinceSelect.value;

        // Clear existing options.
        citySelect.innerHTML = '';

        // Placeholder option.
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a city';
        citySelect.appendChild(placeholder);

        // Add cities for selected province.
        if (cityOptions[province]) {
          cityOptions[province].forEach(function (city) {
            var opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            citySelect.appendChild(opt);
          });
        }

        // Reset selection.
        citySelect.value = '';
      }

      provinceSelect.addEventListener('change', populateCities);

      // Run on load in case a province is preselected.
      populateCities();
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'acumen_form_province_city_dropdown_script' );