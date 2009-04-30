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
                
                public var xcoord:*;
                public var ycoord:*;
                public var zcoord:*;
                
                public var model:Model;
                        
                private var universe:DisplayObject3D		
                private var light:PointLight3D;    

                // the last frame time 
                protected var lastFrame:Date;

                public function EngineManager() { super(); }

                /* interactive scene stuff works like this: 
                        viewport = new Viewport3D(0, 0, true, true);
                        
                        var mam:MovieMaterial = new MovieMaterial(myMovie);
			mam.interactive = true;
			 
			var p:Plane = new Plane(mam, 100, 100);
			p.addEventListener(InteractiveScene3DEvent.OBJECT_OVER, handleMouseOver);
			p.addEventListener(InteractiveScene3DEvent.OBJECT_CLICK, handleMouseClick);
                */

                public function init():void {
                        trace("enginemanager init");
                        // initialize the Papervision 3D engine components
                        viewport = new Viewport3D(Application.application.height, Application.application.width, true); 
                        addChild(viewport);
                        renderer = new BasicRenderEngine();
                        defaultScene = new Scene3D();
                        defaultCamera = new Camera3D(60, 10, 5000, true, true);
                        addEventListener(Event.ENTER_FRAME, draw);
                        light = new PointLight3D();
                        // set the initial frame time
                        lastFrame = new Date();
                        model = new Model();
                        defaultScene.addChild(model);
                        defaultCamera.moveBackward(200);
                        defaultCamera.moveUp(100);
                        trace( 'EngineManager: model.childrenList' );
                        trace( model.getChildByName('elokuvastudio').childrenList() );
                        trace( 'EngineManager: model.childrenList ends' );
                }
                
                protected function draw(event:Event):void {
                        defaultCamera.lookAt(model);
                        // trace('def cam xyz '+defaultCamera.x+','+defaultCamera.y+','+defaultCamera.z);
                        xcoord.text = defaultCamera.x;
                        ycoord.text = defaultCamera.y;
                        zcoord.text = defaultCamera.z;
                        
                        if(defaultCamera.z > 1100) {
                                // north 
                                setTransparentWall(model.northWall);
                        } else if(defaultCamera.z < -1100) {
                                // south
                                setTransparentWall(model.southWall);
                        } else if(defaultCamera.x < 0) {
                                // east
                                setTransparentWall(model.eastWall);
                        } else if(defaultCamera.x > 0) {
                                // west
                                setTransparentWall(model.westWall);
                        }
                        
                        // render the scene
                        renderer.renderScene(defaultScene, defaultCamera, viewport);
                }
                
                protected function setTransparentWall(wallElem:DisplayObject3D):void {
                        model.southWall.alpha = 1.0;    model.northWall.alpha = 1.0; 
                        model.eastWall.alpha = 1.0;     model.westWall.alpha = 1.0;
                        wallElem.alpha = 0.1;
                }
        }
}
