<?php

function renderYachtInfoContainer($yachtData) {
    // Renderiza el contenedor HTML con datos del yate y miniatura
    echo '
        <!-- Yacht Info Display Section -->
        <div class="yacht-info-header">
            <div><i class="fas fa-ruler-horizontal"></i><strong>' . esc_html__( 'Length:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['length']) . '</div>
            <div><i class="fas fa-ship"></i><strong>' . esc_html__( 'Type:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['type']) . '</div>
            <div><i class="fas fa-tools"></i><strong>' . esc_html__( 'Builder:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['builder']) . '</div>
            <div><i class="fas fa-calendar-alt"></i><strong>' . esc_html__( 'Year:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['yearBuilt']) . '</div>
            <div><i class="fas fa-users"></i><strong>' . esc_html__( 'Crew:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['crew']) . '</div>
            <div><i class="fas fa-bed"></i><strong>' . esc_html__( 'Cabins:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['cabins']) . '</div>
            <div><i class="fas fa-user-friends"></i><strong>' . esc_html__( 'Guests:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['guest']) . '</div>
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
                        <a class="nav-link active" id="tab-dimensions" data-bs-toggle="tab" href="#content-dimensions" role="tab" aria-controls="content-dimensions" aria-selected="true">' . esc_html__( 'Dimensions', 'creativoypunto' ) . '</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-accommodation" data-bs-toggle="tab" href="#content-accommodation" role="tab" aria-controls="content-accommodation" aria-selected="false">' . esc_html__( 'Accommodation', 'creativoypunto' ) . '</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-amenities" data-bs-toggle="tab" href="#content-amenities" role="tab" aria-controls="content-amenities" aria-selected="false">' . esc_html__( 'Amenities', 'creativoypunto' ) . '</a>
                     </li>
                     <li class="nav-item" role="presentation">
                         <a class="nav-link" id="tab-performance" data-bs-toggle="tab" href="#content-performance" role="tab" aria-controls="content-performance" aria-selected="false">' . esc_html__( 'Performance', 'creativoypunto' ) . '</a>
                    </li>
                </ul>

                    <!-- Tab panes -->
                    <div class="tab-content yacht-info-tab-content" id="yachtSpecTabContent">
                        <!-- Dimensions -->
                        <div class="tab-pane fade show active" id="content-dimensions" role="tabpanel" aria-labelledby="tab-dimensions">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-ruler-horizontal"></i><strong>' . esc_html__( 'Length:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['length']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-ruler-vertical"></i><strong>' . esc_html__( 'Draft:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['draft'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-arrows-alt-h"></i><strong>' . esc_html__( 'Beam:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['beam'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-weight-hanging"></i><strong>' . esc_html__( 'Gross Tonnage:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['grossTonnage'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation -->
                        <div class="tab-pane fade" id="content-accommodation" role="tabpanel" aria-labelledby="tab-accommodation">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-user-friends"></i><strong>' . esc_html__( 'Guests:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['guest']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-bed"></i><strong>' . esc_html__( 'Cabins:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['cabins']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-users"></i><strong>' . esc_html__( 'Crew:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['crew']) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-door-open"></i><strong>' . esc_html__( 'Cabin Configuration:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['cabinConfiguration'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="tab-pane fade" id="content-amenities" role="tabpanel" aria-labelledby="tab-amenities">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-wifi"></i><strong>' . esc_html__( 'WiFi:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['wifi'] ?? __('Available', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-swimming-pool"></i><strong>' . esc_html__( 'Jacuzzi:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['jacuzzi'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-water"></i><strong>' . esc_html__( 'Water Toys:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['waterToys'] ?? __('Available', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-anchor"></i><strong>' . esc_html__( 'Stabilizers:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['stabilizers'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                            </div>
                        </div>

                        <!-- Performance -->
                        <div class="tab-pane fade" id="content-performance" role="tabpanel" aria-labelledby="tab-performance">
                            <div class="yacht-info-grid">
                                <div class="yacht-info-item">
                                    <i class="fas fa-tachometer-alt"></i><strong>' . esc_html__( 'Cruising Speed:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['cruisingSpeed'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-bolt"></i><strong>' . esc_html__( 'Max Speed:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['maxSpeed'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-gas-pump"></i><strong>' . esc_html__( 'Fuel Consumption:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['fuelConsumption'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                                <div class="yacht-info-item">
                                    <i class="fas fa-route"></i><strong>' . esc_html__( 'Range:', 'creativoypunto' ) . '</strong> ' . esc_html($yachtData['range'] ?? __('N/A', 'creativoypunto')) . '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    ';
}
