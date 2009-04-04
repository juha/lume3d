package
{
	import flash.events.Event;
	import mx.collections.ArrayCollection;
	import mx.core.Application;
	import mx.core.UIComponent;
	import org.papervision3d.cameras.Camera3D;
	import org.papervision3d.materials.utils.MaterialsList;
	import org.papervision3d.render.BasicRenderEngine;
	import org.papervision3d.scenes.Scene3D;
	import org.papervision3d.view.Viewport3D;

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
		// a collection of the BaseObjects 
		protected var baseObjects:ArrayCollection = new ArrayCollection();
		// a collection where new BaseObjects are placed, to avoid adding items 
		// to baseObjects while in the baseObjects collection while it is in a loop
		protected var newBaseObjects:ArrayCollection = new ArrayCollection();
		// a collection where removed BaseObjects are placed, to avoid removing items 
		// to baseObjects while in the baseObjects collection while it is in a loop
		protected var removedBaseObjects:ArrayCollection = new ArrayCollection();
		// the last frame time 
		protected var lastFrame:Date;
		
		public function EngineManager()
		{
			super();
		}
		
		public function startup():void
		{
			// initialize the Papervision 3D engine components
			viewport = new Viewport3D(Application.application.height, Application.application.width, true); 
			addChild(viewport);
			renderer = new BasicRenderEngine();
			defaultScene = new Scene3D();
			defaultCamera = new Camera3D();
			addEventListener(Event.ENTER_FRAME, onEnterFrame);
			
			// set the initial frame time
			lastFrame = new Date();
			
			// create a model to render to the screen
			var material:MaterialsList = new MaterialsList();
			material.addMaterial(ResourceManager.SF02_Tex, "sf-01");
			new MeshObject().startupModelObject(ResourceManager.Fighter1XML, material);
		}
		
		protected function onEnterFrame(event:Event):void 
		{
			// Calculate the time since the last frame
			var thisFrame:Date = new Date();
			var seconds:Number = (thisFrame.getTime() - lastFrame.getTime())/1000.0;
		    	lastFrame = thisFrame;
		    	
		    	// sync the baseObjects collection with any BaseObjects created or removed during the 
		    	// render loop
		    	removeDeletedBaseObjects();
		    	insertNewBaseObjects();
		    	
		    	// allow each BaseObject to update itself
		    	for each (var baseObject:BaseObject in baseObjects)
		    		baseObject.enterFrame(seconds);
		    	
		    	// render the scene
		    	renderer.renderScene(defaultScene, defaultCamera, viewport);
		}
		
		public function addBaseObject(baseObject:BaseObject):void
		{
			newBaseObjects.addItem(baseObject);
		}
		
		public function removeBaseObject(baseObject:BaseObject):void
		{
			removedBaseObjects.addItem(baseObject);
		}
		
		protected function shutdownAll():void
		{
			// don't dispose objects twice
			for each (var baseObject:BaseObject in baseObjects)
			{
				var found:Boolean = false;
				for each (var removedObject:BaseObject in removedBaseObjects)
				{
					if (removedObject == baseObject)
					{
						found = true;
						break;
					}
				}
				
				if (!found)
					baseObject.shutdown();
			}
		}
		
		protected function insertNewBaseObjects():void
		{
			for each (var baseObject:BaseObject in newBaseObjects)
				baseObjects.addItem(baseObject);
			
			newBaseObjects.removeAll();
		}
		
		protected function removeDeletedBaseObjects():void
		{
			for each (var removedObject:BaseObject in removedBaseObjects)
			{
				var i:int = 0;
				for (i = 0; i < baseObjects.length; ++i)
				{
					if (baseObjects.getItemAt(i) == removedObject)
					{
						baseObjects.removeItemAt(i);
						break;
					}
				}
				
			}
			
			removedBaseObjects.removeAll();
		}
	}
}