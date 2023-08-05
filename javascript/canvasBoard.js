    // This script contains routines that will load up a map into a canvas, and will track the territory the mouse is over / 
    // which was last pressed.  It will also allow the user to assign units and SCs to territories, and will display the current
    // assignments on the map.

    // Used to set up sandbox games and for the anti-bot check. Could be used in future for a point a click map that works with all variants.


    // The map which is displayed showing the current assignments and the highlighted territory cursor, refreshed whenever the mouse moves
	const canvasElement = document.getElementById('boardCanvas');
	const ctx = canvasElement.getContext('2d');
	// The map which has territory colors, names, and units drawn on it, but doesn't need to be refreshed when the mouse moves
	const canvasElementOptions = document.getElementById('boardCanvasOptions');
	const ctxOptions = canvasElementOptions.getContext('2d');
	// The base map with different colors in each territory, which can be used to determine which territory the mouse is over and 
	// allows territories to be shaded
	const canvasElementBase = document.getElementById('boardCanvasBase');
	const ctxBase = canvasElementBase.getContext('2d');

	let army = new Image();
	let fleet = new Image();
	let map = new Image();
	let names = new Image();

	// Current unit/sc assignments
	let currentUnitSCState = {};
	// Variant specific country coloring
	let countryColors = [];

	// Store the bounding boxes of each color so that after the first time that color is filled it will only go to the pixels that need to be filled
	let targetColorBoundingBoxes = {};
	
	let variantID = -1;
	let selectedTerrID = -1; // Territory ID that the mouse is currently hovering over / finger is clicking

    // These variables are used when the map is being used to assign units and SCs, for initial board configuration
	let assigningCountryID = 0; // The country ID being assigned, or 0 for none to clear
	let assignmentMode = 0; // 0 = unit and SC, 1 = unit only, 2 = SC only

	// Uses the currently selected country ID, assignment mode, and highlighted territory ID to assign/clear a unit/SC/both to a territory
	function applyAssignment()
	{
		let highlightedTerr = Territories[selectedTerrID];
		let highlightedTerrParent = highlightedTerr;
		if( highlightedTerrParent.coast == 'Child' )
		{
			highlightedTerrParent = Territories[highlightedTerrParent.coastParentID];
		}

		// If we are assigning a unit and this country contains an army we are trying to assign a fleet
		let assigningUnitType = 'Army';
		if( assignmentMode == 2 )
		{
			assigningUnitType = '';
		}
		else if( highlightedTerrParent.type == 'Sea' || highlightedTerr.coast == 'Child' )
		{
			assigningUnitType = 'Fleet';
		}
		else
		{
			for(let optionIndex in currentUnitSCState)
			{
				let option = currentUnitSCState[optionIndex];
				if( option.unitPositionTerrID == highlightedTerrParent.id && option.unitType == 'Army' && option.countryID == assigningCountryID ) {
					assigningUnitType = 'Fleet';
					break;
				}
			}
		}
		// Remove all assignment from a territory
		const clearTerritory = (terrID) => {
			const clearTerritoryByID = (terrID) => {
				for(let optionIndex in currentUnitSCState)
				{
					let option = currentUnitSCState[optionIndex];
					if( option.unitPositionTerrID == terrID ) {
						option.unitPositionTerrID = -1;
					}
					if( option.unitPositionTerrIDParent == terrID ) {
						option.unitPositionTerrIDParent = -1;
					}
					if( option.unitSCTerrID == terrID ) {
						option.unitSCTerrID = -1;
					}
				}
			}
			
			// For any territory ID return an array of territory IDs for the province, to deal with coasts
			const getProvinceTerrIDs = (terrID) => {
				let terr = Territories[terrID];
				if( terr.coast == 'Child' )
				{
					return getProvinceTerrIDs(terr.coastParentID);
				}
				else if( terr.coast == 'Parent' )
				{
					let provinceTerrs = [terrID];
					for(let coastalTerrID in Territories)
					{
						if( coastalTerrID != terrID && Territories[coastalTerrID].coastParentID == terr.id )
							provinceTerrs.push(coastalTerrID);
					}
					return provinceTerrs;
				}
				else
				{
					return [terrID];
				}
			}
			for(let provinceTerrID of getProvinceTerrIDs(terrID))
			{
				clearTerritoryByID(provinceTerrID);
			}
			// Set any records that are empty to no country:
			for(let optionIndex in currentUnitSCState)
			{
				let option = currentUnitSCState[optionIndex];
				if( option.unitPositionTerrID == -1 && option.unitPositionTerrIDParent == -1 && option.unitSCTerrID == -1 )
					option.countryID = -1;
			}
		}
		// First clear any other country's units from this territory
		clearTerritory(selectedTerrID);

		// First process the unit assignment, then process the SC assignment
		if( assignmentMode != 2 ) // If SC only isn't selected process units
		{
			let foundUnit = false;
			if( !foundUnit )
			{
				if( assigningUnitType == 'Army' && (highlightedTerr.coast == 'Child' || highlightedTerr.type == 'Sea') )
				{
				}
				else if( assigningUnitType == 'Fleet' && (highlightedTerr.coast == 'Parent' || highlightedTerr.type == 'Land' ) )
				{
				}
				else
				{
					// No unit found in this territory, so add one
					for(let optionIndex in currentUnitSCState)
					{
						let option = currentUnitSCState[optionIndex];
						if( option.countryID <= 0 || ( option.countryID == assigningCountryID && option.unitPositionTerrID == -1 ) )
						{
							option.countryID = assigningCountryID;
							option.unitType = assigningUnitType;
							option.unitPositionTerrID = selectedTerrID;
							option.unitPositionTerrIDParent = highlightedTerrParent.id;
							// Find and assign an SC
							break;
						}
					}
				}
			}
			selectedTerrID = -1;
		}

		// Now set the SC
		if( assignmentMode != 1 ) // If unit only isn't selected process SC
		{
			let foundSC = false;
			if( !foundSC && highlightedTerrParent.supply == 'Yes' )
			{
				// No unit found in this territory, so add one
				for(let optionIndex in currentUnitSCState)
				{
					let option = currentUnitSCState[optionIndex];
					if( option.countryID <= 0 || ( option.countryID == assigningCountryID && option.unitSCTerrID == -1 ) )
					{
						option.countryID = assigningCountryID;
						option.unitSCTerrID = highlightedTerrParent.id;;
						// Find and assign an SC
						break;
					}
				}
			}
		}
	}

	function drawHighlight()
	{
		const imageData = ctxOptions.getImageData(0, 0, map.width, map.height)
		ctx.putImageData(imageData, 0, 0);

		const drawHighlightMarker = () => {
			if( selectedTerrID <= 0 ) return;
			
			const RGBToHSL = (r, g, b) => {
				r /= 255;
				g /= 255;
				b /= 255;
				const l = Math.max(r, g, b);
				const s = l - Math.min(r, g, b);
				const h = s
					? l === r
					? (g - b) / s
					: l === g
					? 2 + (b - r) / s
					: 4 + (r - g) / s
					: 0;
				return [
					60 * h < 0 ? 60 * h + 360 : 60 * h,
					100 * (s ? (l <= 0.5 ? s / (2 * l - s) : s / (2 - (2 * l - s))) : 0),
					(100 * (2 * l - s)) / 2,
				];
			};
			const HSLToRGB = (h, s, l) => {
				s /= 100;
				l /= 100;
				const k = n => (n + h / 30) % 12;
				const a = s * Math.min(l, 1 - l);
				const f = n =>
					l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
				return [255 * f(0), 255 * f(8), 255 * f(4)];
			};

			const terr = Territories[selectedTerrID];
			if( terr.countryID >= 0 && terr.type != 'Sea' )
			{
				let color = countryColors[assigningCountryID];
				let hsl = RGBToHSL(color[0], color[1], color[2]);
				hsl[2] *= 1.1;
				color = HSLToRGB(hsl[0], hsl[1], hsl[2]);
			}
			
			const selectedColor = countryColors[assigningCountryID];
			const drawCircle = (ctx, x, y, colorArray) => {
				const radius = 5;
				ctx.beginPath();
				ctx.arc(x, y, radius, 0, 2 * Math.PI, false);
				ctx.fillStyle = 'rgb('+colorArray[0]+','+colorArray[1]+','+colorArray[2]+')';
				ctx.fill();
				// Set the width to 1 pixel
				ctx.lineWidth = 1;
				ctx.strokeStyle = 'black';
				ctx.stroke();
			}
			drawCircle(ctx, terr.smallMapX, terr.smallMapY, selectedColor);
		}
		drawHighlightMarker()
	}

	// This will download a variant's territories JS file, execute it, then call loadTerritories to populate the Territories variable
	function downloadAndLoadTerritories(url, callback) {
		// Remove existing script element with the same ID, if it exists
		const existingScriptElement = document.getElementById('loadTerritoriesScript');
		if (existingScriptElement) {
			existingScriptElement.remove();
		}
		const scriptElement = document.createElement('script');
		scriptElement.src = url;
		scriptElement.id = 'loadTerritoriesScript';
		
		scriptElement.onload = function() {
			if (typeof loadTerritories === 'function') {
				loadTerritories();
				Territories = Territories._object; // Undo prototype.js fudge
				callback();
			} else {
				console.error('loadTerritories function not found');
			}
		};
		
		scriptElement.onerror = function() {
			console.error('Error loading script from ' + url);
		};
		
		document.head.appendChild(scriptElement);
	}

	let drawMap = () => {};
	// Loads the variant data and redraws the map for the set variantID value, and calls back once the data is loaded and map drawn
	function loadVariant(callbackOnFirstDraw)
	{
		assigningCountryID = 0;
		targetColorBoundingBoxes = {};
		selectedTerrID = -1;
	
		canvasBoardConfigJS[variantID].fetchTerritories(
			() => {
				// if currentUnitSCState is empty:
				if( Object.keys(currentUnitSCState).length == 0 )
				{
					// set currentUnitSCState to the default options for this variant:
					currentUnitSCState = canvasBoardConfigJS[variantID].getDefaultOptions()
				}
				countryColors = canvasBoardConfigJS[variantID].getCountryColors();
			
				// The army/fleet/name overlay PNGs have white to indicate transparency, so this function is needed to convert to an alpha
				const convertWhiteToTransparent = (image, callback) => {
					// Create a temporary canvas
					const tempCanvas = document.createElement('canvas');
					const tempCtx = tempCanvas.getContext('2d');
				
					// Set the dimensions of the canvas to be the same size as the image
					tempCanvas.width = image.width;
					tempCanvas.height = image.height;
					// Draw the image onto the canvas
					tempCtx.drawImage(image, 0, 0, image.width, image.height);
					let imageData = tempCtx.getImageData(0, 0, image.width, image.height);
					let data = imageData.data;
					for (let i = 0; i < data.length; i += 4) {
						if( data[i] == 255 && data[i + 1] == 255 && data[i + 2] == 255 ) {
							data[i + 3] = 0;
						}
					}
					tempCtx.putImageData(imageData, 0, 0);
					// Create a new Image instance for the true color image
					const trueColorImage = new Image();
				
					// Set up the onload event handler for the true color image
					trueColorImage.onload = function() {
						console.log('True color image loaded successfully:', trueColorImage);
						if (typeof callback === 'function') {
							callback(trueColorImage);
						}
					};
				
					// Set up the onerror event handler for the true color image
					trueColorImage.onerror = function() {
						console.error('Error loading true color image');
					};
				
					// Set the src property of the true color image to the data URL of the canvas
					trueColorImage.src = tempCanvas.toDataURL();
				};
			
				let remainingToLoad = 4;
				army.onload = () => convertWhiteToTransparent(army, (im) => {
					army = im;
					if(--remainingToLoad == 0)
					{
						drawMap();
					}
				});
				fleet.onload = () => convertWhiteToTransparent(fleet, (im) => {
					fleet = im;
					if(--remainingToLoad == 0)
					{
						drawMap();
					}
				});
				names.onload = () => convertWhiteToTransparent(names, (im) => {
					names = im;
					if(--remainingToLoad == 0)
					{
						drawMap();
					}
				});
				map.onload = () => {
					if(--remainingToLoad == 0)
					{
						drawMap();
					}
				};
				army.src = canvasBoardConfigJS[variantID].armyURL;
				fleet.src = canvasBoardConfigJS[variantID].fleetURL;
				map.src = canvasBoardConfigJS[variantID].mapURL;
				names.src = canvasBoardConfigJS[variantID].namesURL;
			
				// Reload the table, check that the current setup is valid, check for any variant specific rules etc
				drawMap = () => {
					const loadMapIntoCanvas = () => {
						canvasElementBase.width = map.width;
						canvasElementBase.height = map.height;
						ctxBase.drawImage(map, 0, 0);

						canvasElementOptions.width = map.width;
						canvasElementOptions.height = map.height;
						ctxOptions.drawImage(map, 0, 0);

						canvasElement.width = map.width;
						canvasElement.height = map.height;
						ctx.drawImage(map, 0, 0);
					};
					const drawcurrentUnitSCState = () => {
						/*
						let supplyCenterTargetOptions = this.getEmptyOptions(); // [{index, countryID, unitPositionTerrID, unitPositionTerrIDParent, unitSCTerrID, unitType}]
						let countryUnits = this.getCountryUnits(); // {countryName: {territoryName, unitType}}
						let supplyCenters = this.getSupplyCenters(); // {terrID: {id, name, type, supply, countryID, coast, coastParentID}}
						*/
						const colorAllPixelsWithSameColor = (ctx, x, y, fillColor) => {
							const canvasWidth = ctx.canvas.width;
							const canvasHeight = ctx.canvas.height;

							let xStart = 0;
							let yStart = 0;
							let boxWidth = canvasWidth;
							let boxHeight = canvasHeight;

							// Get the color of the pixel at (x, y)
							const targetColor = ctxBase.getImageData(x, y, 1, 1).data;

							let hasBoundingBox = false;
							const targetColorKey = targetColor.join('-');
							if( targetColorKey in targetColorBoundingBoxes )
							{
								let boundingBox = targetColorBoundingBoxes[targetColorKey];
								xStart = boundingBox.xStart;
								yStart = boundingBox.yStart;
								boxWidth = boundingBox.boxWidth;
								boxHeight = boundingBox.boxHeight;
								hasBoundingBox = true;
							}

							// Get the entire canvas pixel data
							const imageDataBase = ctxBase.getImageData(xStart, yStart, boxWidth, boxHeight);
							const dataBase = imageDataBase.data;
							const imageData = ctx.getImageData(xStart, yStart, boxWidth, boxHeight);
							const data = imageData.data;

							// Convert the fillColor to an RGBA array
							const colorArray = fillColor;// .match(/\d+/g).map(Number);
							if (colorArray.length === 3) colorArray.push(255);

							// Iterate over all pixels and replace the target color with the fill color
							let minX = canvasWidth;
							let minY = canvasHeight;
							let maxX = 0;
							let maxY = 0;
							for (let i = 0; i < data.length; i += 4) {
								if (dataBase[i] === targetColor[0] && dataBase[i + 1] === targetColor[1] && dataBase[i + 2] === targetColor[2] && dataBase[i + 3] === targetColor[3]) {
									data[i] = colorArray[0];
									data[i + 1] = colorArray[1];
									data[i + 2] = colorArray[2];
									data[i + 3] = colorArray[3];
									
									// Get the coordinates to find the bounding box
									const xPosition = (i / 4) % canvasWidth;
									const yPosition = Math.floor((i / 4) / canvasWidth);

									// Add 5 pixes around the bounding box to make sure that the fill doesn't miss any pixels
									minX = Math.min(minX, Math.max(0,xPosition - 5));
									minY = Math.min(minY, Math.max(0,yPosition - 5));
									maxX = Math.max(maxX, Math.min(canvasWidth, xPosition + 5));
									maxY = Math.max(maxY, Math.min(canvasHeight, yPosition + 5));
								}
							}

							if( ! hasBoundingBox )
							{
								targetColorBoundingBoxes[targetColorKey] = {
									xStart: minX,
									yStart: minY,
									boxWidth: maxX - minX,
									boxHeight: maxY - minY
								};	
							}

							// Put the modified pixel data back onto the canvas
							ctx.putImageData(imageData, xStart, yStart);
						};
						const defaultColor = countryColors[0];
						// Do initial base color
						for (const terrID in Territories)
						{
							const terr = Territories[terrID];
							if( terr.type != 'Sea' )
							{
								colorAllPixelsWithSameColor(ctxOptions, terr.smallMapX, terr.smallMapY, defaultColor);
							}
						}
						// Color SCs that are occupied:
						for (const optionInd in currentUnitSCState)
						{
							const option = currentUnitSCState[optionInd];
							if( option.countryID > 0 )
							{
								const color = countryColors[option.countryID];
								if( option.unitSCTerrID > 0 )
								{
									let terr = Territories[option.unitSCTerrID];
									colorAllPixelsWithSameColor(ctxOptions, terr.smallMapX, terr.smallMapY, color);
								}
							}
						}
						// Draw names
						ctxOptions.drawImage(names, 0, 0, names.width, names.height);

						const drawImageCentered = (ctx, image, x, y) => {
							const imgWidth = image.width;
							const imgHeight = image.height;
							ctx.drawImage(image, x - imgWidth / 2, y - imgHeight / 2, imgWidth, imgHeight);
						};
						const drawRect = (ctx, x, y, colorArray) => {
							x = Math.round(x)+0.5; // 0.5 needed to avoid blurry lines
							y = Math.round(y)+0.5;
							ctx.beginPath();
							ctx.rect(x, y-10, 10, 10);
							ctx.fillStyle = 'rgb('+colorArray[0]+','+colorArray[1]+','+colorArray[2]+')';
							ctx.fill();
							ctx.lineWidth = 1;
							ctx.strokeStyle = 'rgb(0,0,0)';
							ctx.stroke();
						};
						// Draw unit icons:
						for (const optionInd in currentUnitSCState)
						{
							const option = currentUnitSCState[optionInd];
							if( option.countryID > 0 )
							{
								const color = countryColors[option.countryID];
								if( option.unitPositionTerrID > 0 && ( option.unitType === 'Army' || option.unitType === 'Fleet' ) )
								{
									let terr = Territories[option.unitPositionTerrID];
									drawRect(ctxOptions, terr.smallMapX, terr.smallMapY, color);
									drawImageCentered(ctxOptions, option.unitType == 'Army' ? army : fleet, terr.smallMapX, terr.smallMapY);
								}
							}
						}
						drawHighlight();
					}
					loadMapIntoCanvas();
					drawcurrentUnitSCState();

					if( typeof callbackOnFirstDraw === 'function' )
					{
                        let firstCallback = callbackOnFirstDraw;
						callbackOnFirstDraw = null;
						firstCallback(); // Prevent infinite recursion if the callback redraws the map
					}
				};
			}
		);
	}

	// Add a mousemove event listener to the image element
	canvasElement.addEventListener('mousemove', (event) => {
		const findClosestTerritory = (Territories, x, y) => {
			let closestTerritory = null;
			let minDistance = Infinity;

			for (const key in Territories) {
				const territory = Territories[key];
				const { smallMapX, smallMapY } = territory;

				// Calculate the Euclidean distance between (x, y) and (smallMapX, smallMapY)
				const distance = Math.sqrt(Math.pow(x - smallMapX, 2) + Math.pow(y - smallMapY, 2));

				// If the calculated distance is smaller than the current minimum distance,
				// update the closestTerritory and minDistance
				if (distance < minDistance) {
					closestTerritory = territory;
					minDistance = distance;
				}
			}

			return closestTerritory;
		}
		// Calculate the x and y coordinates relative to the image
		const rect = canvasElement.getBoundingClientRect();
		const x = event.clientX - rect.left;
		const y = event.clientY - rect.top;
		const terr = findClosestTerritory(Territories, x, y);

		if( terr.id != selectedTerrID )
		{
			selectedTerrID = terr.id;
			drawHighlight();
		}
	});
