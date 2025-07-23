<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Debug Template Designer</title>
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .canvas { border: 2px dashed #2196f3; width: 594px; height: 420px; position: relative; margin: 20px auto; }
        button { padding: 8px 16px; background: #2196f3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1976d2; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Debug Template Designer</h1>
            
            <div x-data="{
                templateData: {
                    width: 297, 
                    height: 210,
                    elements: []
                },
                selectedElement: null,
                
                addText() {
                    this.templateData.elements.push({
                        type: 'text',
                        content: '@{{ participant_name }}',
                        x: 100,
                        y: 100,
                        fontSize: 24,
                        fontFamily: 'Arial',
                        id: Date.now()
                    });
                }
            }">
                <div class="card">
                    <button @click="addText()">Add Text Element</button>
                </div>
                
                <div class="card">
                    <h3>Template Data:</h3>
                    <pre x-text="JSON.stringify(templateData, null, 2)"></pre>
                </div>
                
                <div class="card">
                    <div class="canvas" @click="console.log('Canvas clicked')">
                        <template x-for="element in templateData.elements" :key="element.id">
                            <div
                                x-show="element.type === 'text'"
                                :style="`position: absolute; left: ${element.x}px; top: ${element.y}px; font-size: ${element.fontSize}px; font-family: ${element.fontFamily}`"
                                x-text="element.content"
                            ></div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add debug logging
        console.log('Debug template loaded');
    </script>
</body>
</html> 