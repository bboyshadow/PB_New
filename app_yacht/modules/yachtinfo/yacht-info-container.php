<?php

function renderYachtInfoContainer($yachtData) {
    // Renderiza el contenedor HTML con datos del yate y miniatura
    echo '
        <!-- Yacht Info Display Section -->
        <div class="yacht-info-header">
            <div><i class="fas fa-ruler-horizontal"></i><strong>Length:</strong> ' . esc_html($yachtData['length']) . '</div>
            <div><i class="fas fa-ship"></i><strong>Type:</strong> ' . esc_html($yachtData['type']) . '</div>
            <div><i class="fas fa-tools"></i><strong>Builder:</strong> ' . esc_html($yachtData['builder']) . '</div>
            <div><i class="fas fa-calendar-alt"></i><strong>Year:</strong> ' . esc_html($yachtData['yearBuilt']) . '</div>
            <div><i class="fas fa-users"></i><strong>Crew:</strong> ' . esc_html($yachtData['crew']) . '</div>
            <div><i class="fas fa-bed"></i><strong>Cabins:</strong> ' . esc_html($yachtData['cabins']) . '</div>
            <div><i class="fas fa-user-friends"></i><strong>Guests:</strong> ' . esc_html($yachtData['guest']) . '</div>
        </div>
        <div class="yacht-info-body pb-0">
            <div class="yacht-image-container mb-0">
                <img src="' . esc_url($yachtData['imageUrl']) . '" alt="' . esc_attr($yachtData['yachtName']) . '" class="yacht-image">
                <h3 class="yacht-name">' . esc_html($yachtData['yachtName']) . '</h3>
            </div>
            <div class="yacht-details mb-0">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs yacht-info-tabs" id="yachtSpecTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-dimensions" data-bs-toggle="tab" href="#content-dimensions" role="tab" aria-controls="content-dimensions" aria-selected="true">Dimensions</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-accommodation" data-bs-toggle="tab" href="#content-accommodation" role="tab" aria-controls="content-accommodation" aria-selected="false">Accommodation</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-amenities" data-bs-toggle="tab" href="#content-amenities" role="tab" aria-controls="content-amenities" aria-selected="false">Amenities</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-performance" data-bs-toggle="tab" href="#content-performance" role="tab" aria-controls="content-performance" aria-selected="false">Performance</a>
                    </li>
                </ul>

                    <!-- Tab panes -->
                    <div class="tab-content yacht-info-tab-content" id="yachtSpecTabContent">
                        <!-- Dimensions -->
                        <div class="tab-pane fade show active" id="content-dimensions" role="tabpanel" aria-labelledby="tab-dimensions">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-ruler-horizontal"></i><strong>Length:</strong> ' . esc_html($yachtData['length']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-ruler-vertical"></i><strong>Draft:</strong> ' . esc_html($yachtData['draft'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-arrows-alt-h"></i><strong>Beam:</strong> ' . esc_html($yachtData['beam'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-weight-hanging"></i><strong>Gross Tonnage:</strong> ' . esc_html($yachtData['grossTonnage'] ?? 'N/A') . '
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation -->
                        <div class="tab-pane fade" id="content-accommodation" role="tabpanel" aria-labelledby="tab-accommodation">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-user-friends"></i><strong>Guests:</strong> ' . esc_html($yachtData['guest']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-bed"></i><strong>Cabins:</strong> ' . esc_html($yachtData['cabins']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-users"></i><strong>Crew:</strong> ' . esc_html($yachtData['crew']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-door-open"></i><strong>Cabin Configuration:</strong> ' . esc_html($yachtData['cabinConfiguration'] ?? 'N/A') . '
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="tab-pane fade" id="content-amenities" role="tabpanel" aria-labelledby="tab-amenities">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-wifi"></i><strong>WiFi:</strong> ' . esc_html($yachtData['wifi'] ?? 'Available') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-swimming-pool"></i><strong>Jacuzzi:</strong> ' . esc_html($yachtData['jacuzzi'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-water"></i><strong>Water Toys:</strong> ' . esc_html($yachtData['waterToys'] ?? 'Available') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-anchor"></i><strong>Stabilizers:</strong> ' . esc_html($yachtData['stabilizers'] ?? 'N/A') . '
                                </div>
                            </div>
                        </div>

                        <!-- Performance -->
                        <div class="tab-pane fade" id="content-performance" role="tabpanel" aria-labelledby="tab-performance">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-tachometer-alt"></i><strong>Cruising Speed:</strong> ' . esc_html($yachtData['cruisingSpeed'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-bolt"></i><strong>Max Speed:</strong> ' . esc_html($yachtData['maxSpeed'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-gas-pump"></i><strong>Fuel Consumption:</strong> ' . esc_html($yachtData['fuelConsumption'] ?? 'N/A') . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-route"></i><strong>Range:</strong> ' . esc_html($yachtData['range'] ?? 'N/A') . '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    ';
}
