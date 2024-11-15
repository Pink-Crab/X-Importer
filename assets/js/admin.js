/**
 * Admin JS
 * 
 * @package 	PinkCrab X Importer
 */
document.addEventListener("DOMContentLoaded", () => {
    // Localized data.
    const plugin = pinkcrab_x_importer_admin_js;

    // Elements
    const newImport = document.getElementById("pc_x_new_submit");

    // Event delegation for actions menu
    newImport.addEventListener("click", handleNewImport);

    /**
     * Handle the click event on the new import button
     * 
     * @param {Event} event 
     */
    function handleNewImport(event) {
        event.preventDefault();

        /**
         * Compiles the form data,
         * 
         * @returns {format:string, duplicated:string, nonce:string}
         */
        function compileFormData() {
            const compiled = new FormData();
            compiled.append("format", document.getElementById("pc_x_new_format").value);
            compiled.append("duplicated", document.getElementById("pc_x_new_duplicate").value);
            compiled.append("nonce", document.getElementById("pc_x_new_nonce").value);
            compiled.append("file", document.getElementById("pc_x_new_file").files[0]);
            compiled.append("action", plugin.new_import_action);
            return compiled;
        }

        fetchData(compileFormData(), updateContent);
        console.log("clicked");
    }



    /**
     * Fetch data from the server
     * @param {object} data - The form data to send
     * @param {Function} callback - The callback to handle the response
     */
    function fetchData(data, callback) {
        console.log(plugin, data);

        // Do a post request to the server
        fetch(plugin.ajax_url, {
            method: "POST",
            body: data,
        })
        .then((response) => response.json())
        .then((data) => {
            callback(data);
        })
        .catch((error) => {
            console.error("Error:", error);
        });
    }

    /**
     * Update the main content area
     * @param {string} content - The HTML content to display
     */
    function updateContent(content) {
        mainContent.innerHTML = content;
    }


});