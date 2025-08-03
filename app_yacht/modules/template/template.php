<!------------------------ ARCHIVO modules\template\template.php ------------------------>
<div class="col-md-12 d-flex flex-column">
    <div class="custom-template-container">
        <!-- Row para los campos y botones -->
        <div class="row d-flex justify-content-start align-items-stretch mt-3">

           

            <!-- Botón Copy Template -->
            <div class="col-6 col-md-4 col-lg-2 p-1">
                <button id="insertTemplate" class="btn btn-info w-100">Send Template</button>
            </div>
            <div class="col-6 col-md-4 col-lg-2 p-1">
                <button type="button" class="btn btn-info w-100" id="copyTemplateButton" onclick="copyTemplate()" disabled>Copy Template</button>
            </div>

            <!-- Selector de Templates -->
            <div class="col-6 col-md-4 col-lg-2 p-1">
                <label for="templateSelector" class="visually-hidden">Select Template</label> <!-- Visually hidden label -->
                <select id="templateSelector" class="form-control">
                    <option value="" selected>Select Template:</option>
                    <option value="">Clear Template</option>
                    <option value="default-template">Default Template</option>
                    <option value="template-01">Template 01</option>
                    <option value="template-02">Template 02</option>
                </select>
            </div>

            <!-- Nuevo selector: Save Templates -->
            <div class="col-6 col-md-4 col-lg-2 p-1">
                <label for="saveTemplateSelector" class="visually-hidden">Load Saved Template</label> <!-- Visually hidden label -->
                <select id="saveTemplateSelector" class="form-control">
                    <option value="" selected>Saved Templates</option>
                    <!-- Opciones para los templates guardados -->
                </select>
            </div>

            <!-- Botón para guardar el template -->
            <div class="col-6 col-md-4 col-lg-2 p-1">
                <button id="saveTemplateButton" class="btn btn-primary w-100">Save Template</button>
            </div>
        </div>
    </div>
</div>
