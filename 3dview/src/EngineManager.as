package
{
	import flash.events.Event;
	import mx.core.Application;
	import mx.core.UIComponent;
    
	import org.papervision3d.cameras.Camera3D;
	import org.papervision3d.render.BasicRenderEngine;
	import org.papervision3d.scenes.Scene3D;
	import org.papervision3d.view.Viewport3D;
    
    import org.papervision3d.lights.PointLight3D;
    import org.papervision3d.objects.DisplayObject3D;
        
	public class EngineManager extends UIComponent
	{
		// Papervision viewport
		public var viewport:Viewport3D = null;
		// Papervision rendering engine
		public var renderer:BasicRenderEngine = null;
		// Papervision scene
		public var defaultScene:Scene3D = null;
		// Papervision camera
		public var defaultCamera:Camera3D = null;
                
        public var model:Model;
                
        private var universe:DisplayObject3D		
        private var light:PointLight3D;    
            	
        // the last frame time 
		protected var lastFrame:Date;
		
		public function EngineManager() { super(); }
		
		public function init():void {
            trace("enginemanager init");
            // initialize the Papervision 3D engine components
			viewport = new Viewport3D(Application.application.height, Application.application.width, false); 
			addChild(viewport);
			renderer = new BasicRenderEngine();
			defaultScene = new Scene3D();
			defaultCamera = new Camera3D();
			addEventListener(Event.ENTER_FRAME, draw);
			
            light = new PointLight3D();
            
			// set the initial frame time
			lastFrame = new Date();
			model = new Model();
            defaultScene.addChild(model);
            // model.x = 0;
            model.y = 500;
            model.x = 300;
            model.scale = 2.5;
		}
		
		protected function draw(event:Event):void {
			// Calculate the time since the last frame
			// var thisFrame:Date = new Date();
			// var seconds:Number = (thisFrame.getTime() - lastFrame.getTime())/1000.0;
			// lastFrame = thisFrame;
                        
            // model.yaw(1);
            // model.pitch(1);
            // model.roll(1);
                        
            // render the scene
			renderer.renderScene(defaultScene, defaultCamera, viewport);
		}
		
	}
}
