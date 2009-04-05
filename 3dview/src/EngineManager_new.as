package 
{
	import flash.events.Event;
	import mx.collections.ArrayCollection;
	import mx.core.Application;
        import mx.core.UIComponent;
    
	//import main papervision assets
	import org.papervision3d.cameras.Camera3D;
	import org.papervision3d.lights.PointLight3D;
	import org.papervision3d.render.BasicRenderEngine;
	import org.papervision3d.scenes.Scene3D;
	import org.papervision3d.view.Viewport3D;

        import org.papervision3d.materials.utils.MaterialsList;
        import org.papervision3d.materials.MovieMaterial;
        
    
	
	//import objects
	import org.papervision3d.objects.DisplayObject3D;
	import org.papervision3d.objects.parsers.DAE;
        import org.papervision3d.objects.parsers.Collada;    
	
	//import event listener
	import org.papervision3d.events.FileLoadEvent;
	
	
	public class EngineManager extends UIComponent 
	{
		
                [Embed (source="../media/uv_face_cube.png")]
                public static const SF02:Class;
                public  static const SF02_Tex:MovieMaterial = new MovieMaterial(new SF02());
                // BitmapFileMaterial('../media/uv_face_cube.png');
                
        
		//papervision main assets
		private var scene:Scene3D;
		private var viewport:Viewport3D;
		private var camera:Camera3D;
		private var light:PointLight3D;
		private var renderer:BasicRenderEngine;
		
		//other things needed for dae
		private var universe:DisplayObject3D;
		private var daeFile:Collada;
		
		private var bl:Number;
		private var bt:Number;
		private var per:Number;
	
		public function EngineManager() { super(); }
		
                public function startup():void 
                {
        		//Setup viewport, add to stage
			viewport = new Viewport3D(Application.application.height, Application.application.width, true); 
                        addChild(viewport);
			
                        parentDocument.debugText.text = 'startup';
                        
			//Setup renderer
			renderer = new BasicRenderEngine();
			
			// Setup camera
			camera = new Camera3D();
			// camera.z = - 200;
			// camera.zoom = 12;
			
			//Setup light
			// light = new PointLight3D(false);
			
			//Setup scene
			scene = new Scene3D();
			
			//add dae object
			daeFile = new Collada();
                        parentDocument.debugText.text = 'startup 1';
                        
                        // create a model to render to the screen
                        var material:MaterialsList = new MaterialsList();
                        material.addMaterial(SF02_Tex, "uv_face_cube_png");
			
                        daeFile.load("../media/simple_cube.dae", material);
			parentDocument.debugText.text = 'startup 2';
                        
			// daeFile.scaleX = daeFile.scaleY = 20;
			// daeFile.scaleZ = 80;
			
                        parentDocument.debugText.text = 'startup 3';
                        
			//add loading listeners to your dae
			// daeFile.addEventListener(FileLoadEvent.LOAD_COMPLETE, handleLoadComplete);
			// daeFile.addEventListener(FileLoadEvent.LOAD_ERROR, handleLoadError);
			// daeFile.addEventListener(FileLoadEvent.LOAD_PROGRESS, handleProgress);
			// daeFile.addEventListener(FileLoadEvent.SECURITY_LOAD_ERROR, handleSecurity);
			// daeFile.addEventListener(FileLoadEvent.COLLADA_MATERIALS_DONE, handleMaterialsDone);
			
                        // Setup container, add dae to container, add container to scene.
			universe = new DisplayObject3D();
                        // daeFile.x = -20; 
                        // daeFile.y = -20;
			trace('x: '+daeFile.x+' y:'+daeFile.y);
                        
                        daeFile.x = 5; daeFile.y = 5;
                        universe.addChild(daeFile);
                        
                        scene.addChild(universe);
			
			//Listen to enter frame
			addEventListener(Event.ENTER_FRAME, onEnterFrame);
                        parentDocument.debugText.text = 'startup done';
                        
		}
		
		
                private function handleLoadComplete(e:Event):void {
                        // trace("IN EVENT LISTENER, LOAD COMPLETE");
			// messageText.text = "COLLADA LOAD COMPLETE";
		};
		
		private function handleLoadError(e:Event):void
		{
			// messageText.text = "THERE HAS BEEN A LOADING ERROR";
		};
		
		private function handleProgress(e:Event):void
		{
			//bl = e.target.bytesLoaded;
			//bt = e.target.bytesTotal;
			//per = Math.round(bl/bt*100);
			//messageText.text = "COLLADA "+per+"% LOADED, PLEASE WAIT";
		};
		
		private function handleSecurity(e:Event):void
		{
			//messageText.text = "THERE HAS BEEN A SECURITY ERROR";
		};
		
		private function handleMaterialsDone(e:Event):void
		{
			//messageText.text = "COLLADA MATERIALS LOAD COMPLETE";
		};
		
		protected function onEnterFrame(event:Event):void 
                {
                	//rotate and render!
                        parentDocument.debugText.text = 'render';
                        universe.yaw(2);
			universe.pitch(0.2);
			universe.roll(0.2);
                        universe.moveBackward(1);
                        universe.moveUp(1);
			renderer.renderScene(scene,camera,viewport);
			
		}
	}
}
