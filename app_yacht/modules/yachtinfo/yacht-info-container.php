<?php

function renderYachtInfoContainer($yachtData) {
    // Renderiza el contenedor HTML con datos del yate y miniatura
    echo '<div id="yacht-info-container" class="card bg-dark text-light border-0 shadow mb-3" style="border-radius: 12px; overflow: hidden;">
        <!-- Yacht URL Input Section -->
        <div class="card-header bg-dark p-3" style="border-bottom: 1px solid #495057;">
             <div class="row align-items-end">
                 <div class="col-md-9">
                     <label for="yachtUrl" class="form-label text-info mb-1" style="font-size: 0.9rem; font-weight: 600;">Yacht URL</label>
                     <input type="url" class="form-control bg-secondary text-light border-0" id="yachtUrl" placeholder="Enter yacht listing URL..." style="font-size: 0.9rem; color: #e0e0e0 !important;">
                 </div>
                 <div class="col-md-3">
                     <button type="button" class="btn btn-info w-100" id="getYachtInfo" style="font-size: 0.9rem; font-weight: 600;">
                         <i class="fas fa-download me-1"></i>Get Info
                     </button>
                 </div>
             </div>
         </div>
        <div class="card-body d-flex align-items-center p-3">
            <img src="' . esc_url($yachtData['imageUrl']) . '" alt="' . esc_attr($yachtData['yachtName']) . '" class="img-fluid rounded me-3" style="width: 140px; height: 90px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
            <div class="flex-grow-1">
                <h5 class="card-title mb-2" style="font-weight: 600;">' . esc_html($yachtData['yachtName']) . '</h5>
                <div class="d-flex justify-content-start align-items-center gap-4 text-sm flex-wrap">
                    <div class="d-flex align-items-center"><i class="fas fa-ruler-horizontal me-1 text-muted"></i><strong>Longitud:</strong> ' . esc_html($yachtData['length']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-ship me-1 text-muted"></i><strong>Tipo:</strong> ' . esc_html($yachtData['type']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-tools me-1 text-muted"></i><strong>Constructor:</strong> ' . esc_html($yachtData['builder']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-calendar-alt me-1 text-muted"></i><strong>Año:</strong> ' . esc_html($yachtData['yearBuilt']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-users me-1 text-muted"></i><strong>Tripulación:</strong> ' . esc_html($yachtData['crew']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-bed me-1 text-muted"></i><strong>Cabinas:</strong> ' . esc_html($yachtData['cabins']) . '</div>
                    <div class="d-flex align-items-center"><i class="fas fa-user-friends me-1 text-muted"></i><strong>Huéspedes:</strong> ' . esc_html($yachtData['guest']) . '</div>
                </div>
                <div id="specifications-tab" class="mt-3">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-fill" id="yachtSpecTabs" role="tablist" style="border-bottom: 1px solid #495057;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-info" id="tab-dimensions" data-bs-toggle="tab" data-bs-target="#content-dimensions" type="button" role="tab" aria-controls="content-dimensions" aria-selected="true" style="background: transparent; border: none; font-size: 0.85rem; font-weight: 600;">Dimensions</button>
                         </li>
                         <li class="nav-item" role="presentation">
                             <button class="nav-link text-info" id="tab-accommodation" data-bs-toggle="tab" data-bs-target="#content-accommodation" type="button" role="tab" aria-controls="content-accommodation" aria-selected="false" style="background: transparent; border: none; font-size: 0.85rem; font-weight: 600;">Accommodation</button>
                         </li>
                         <li class="nav-item" role="presentation">
                             <button class="nav-link text-info" id="tab-amenities" data-bs-toggle="tab" data-bs-target="#content-amenities" type="button" role="tab" aria-controls="content-amenities" aria-selected="false" style="background: transparent; border: none; font-size: 0.85rem; font-weight: 600;">Amenities</button>
                         </li>
                         <li class="nav-item" role="presentation">
                             <button class="nav-link text-info" id="tab-performance" data-bs-toggle="tab" data-bs-target="#content-performance" type="button" role="tab" aria-controls="content-performance" aria-selected="false" style="background: transparent; border: none; font-size: 0.85rem; font-weight: 600;">Performance</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content mt-2" id="yachtSpecTabContent">
                        <!-- Dimensions -->
                        <div class="tab-pane fade show active" id="content-dimensions" role="tabpanel" aria-labelledby="tab-dimensions">
                            <div class="d-flex flex-wrap gap-3">
                                <span class="d-flex align-items-center"><i class="fas fa-ruler-combined text-info me-1"></i><strong class="text-light">Beam:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["beam"] ?? "38.5" ) . '</span></span>
                                <span class="d-flex align-items-center"><i class="fas fa-water text-info me-1"></i><strong class="text-light">Draft:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["draft"] ?? "6.9" ) . '</span></span>
                            </div>
                        </div>

                        <!-- Accommodation -->
                        <div class="tab-pane fade" id="content-accommodation" role="tabpanel" aria-labelledby="tab-accommodation">
                            <div class="d-flex flex-wrap gap-3">
                                <span class="d-flex align-items-center"><i class="fas fa-bed text-info me-1"></i><strong class="text-light">Cabin Configuration:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["cabinConfiguration"] ?? "1 King(s), 3 Queen(s)" ) . '</span></span>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="tab-pane fade" id="content-amenities" role="tabpanel" aria-labelledby="tab-amenities">
                            <div class="d-flex flex-wrap gap-3">
                                <span class="d-flex align-items-center"><i class="fas fa-fan text-info me-1"></i><strong class="text-light">A/C:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["ac"] ?? "Full" ) . '</span></span>
                                <span class="d-flex align-items-center"><i class="fas fa-hot-tub text-info me-1"></i><strong class="text-light">Jacuzzi:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["jacuzzi"] ?? "Yes" ) . '</span></span>
                                <span class="d-flex align-items-center"><i class="fas fa-helicopter text-info me-1"></i><strong class="text-light">Helipad:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["helipad"] ?? "No" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-sink text-info me-1"></i><strong class="text-light">Wash Basins:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["washBasins"] ?? "4" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-shower text-info me-1"></i><strong class="text-light">Showers:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["showers"] ?? "4" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-bath text-info me-1"></i><strong class="text-light">Tubs:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["tubs"] ?? "0" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-toilet text-info me-1"></i><strong class="text-light">Heads:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["heads"] ?? "0" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-toilet text-info me-1"></i><strong class="text-light">Elec. Heads:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["elecHeads"] ?? "4" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-toilet text-info me-1"></i><strong class="text-light">TP in Heads:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["tpInHeads"] ?? "Yes" ) . '</span></span>
                            </div>
                        </div>

                        <!-- Performance -->
                        <div class="tab-pane fade" id="content-performance" role="tabpanel" aria-labelledby="tab-performance">
                            <div class="d-flex flex-wrap gap-3">
                                <span class="d-flex align-items-center"><i class="fas fa-tachometer-alt text-info me-1"></i><strong class="text-light">Cruising Speed:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["cruiseSpeed"] ?? "8 kts" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-tachometer-alt text-info me-1"></i><strong class="text-light">Max Speed:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["maxSpeed"] ?? "12 kts" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-engine text-info me-1"></i><strong class="text-light">Engines/Generators:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["engines"] ?? "300hp x 2 (600hp total) 32kw x 2" ) . '</span></span>
                                 <span class="d-flex align-items-center"><i class="fas fa-gas-pump text-info me-1"></i><strong class="text-light">Fuel Capacity:</strong>&nbsp;<span class="text-secondary">' . esc_html( $yachtData["fuelCapacity"] ?? "[Value]" ) . '</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ';
}
