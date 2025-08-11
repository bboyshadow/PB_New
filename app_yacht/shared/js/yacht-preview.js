document.addEventListener(
	'DOMContentLoaded',
	function() {
		const yachtUrlInput  = document.getElementById( 'yachtUrl' );
		const fetchBtn       = document.getElementById( 'fetchYachtBtn' );
		const yachtPreview   = document.getElementById( 'yachtPreview' );
		const yachtThumbnail = document.getElementById( 'yachtThumbnail' );
		const yachtInfo      = document.getElementById( 'yachtInfo' );

		fetchBtn.addEventListener(
			'click',
			function() {
				const url = yachtUrlInput.value.trim();
				if (url) {
					fetchYachtInfo( url );
				}
			}
		);

		function fetchYachtInfo(url) {
			const data = {
				action: 'fetch_yacht_info',
				nonce: yachtPreviewData.nonce,
				url: url
			};

			jQuery.post(
				yachtPreviewData.ajaxurl,
				data,
				function(response) {
					if (response.success) {
						const info               = response.data;
						yachtThumbnail.innerHTML = info.imageUrl ? ` < img src = "${info.imageUrl}" alt = "Yacht Thumbnail" class = "img-thumbnail rounded" style = "max-width: 100px; height: auto;" > ` : 'No imagen';
						yachtInfo.innerHTML      = '';
						const fields             = {
							'Name': info.yachtName,
							'Length': info.length,
							'Type': info.type,
							'Builder': info.builder,
							'Year Built': info.yearBuilt,
							'Crew': info.crew,
							'Cabins': info.cabins,
							'Guests': info.guest,
							'Cabin Configuration': info.cabinConfiguration
						};
						for (const [field, value] of Object.entries( fields )) {
							if (value && value !== '--') {
								yachtInfo.innerHTML += ` < li class = "mb-1" > ${field} - ${value || 'N/A'} < / li > `;
							}
						}
						yachtPreview.style.display = 'block';
					} else {
						yachtInfo.innerHTML        = 'Error fetching info: ' + (response.data.message || 'Unknown');
						yachtPreview.style.display = 'block';
					}
				}
			);
		}
	}
);
