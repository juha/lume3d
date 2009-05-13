package
{
        import flash.utils.ByteArray;
        
        import mx.core.Application;
        import org.papervision3d.materials.utils.MaterialsList;
        import org.papervision3d.materials.MovieMaterial;
        import org.papervision3d.objects.DisplayObject3D;
        import org.papervision3d.objects.parsers.Collada;
        
        public class Model extends Collada {
                
                // Embed the resources so that we don't rely on external files. Also makes testing easier
	        // since you don't need to either tweak flash-access rules or have a webserver
	        [Embed (source="../media/elokuvastudio.dae", mimeType="application/octet-stream")]
	        public static const meshData:Class;
	        
	        public static const meshXML:XML = function():XML { 
	                      var byteArray:ByteArray = new meshData() as ByteArray; 
	                      return new XML(byteArray.readUTFBytes(byteArray.length));
	              }();
	        
	        [Embed (source="../media/uv_face_cube.png")]
	        public static const materialData:Class;
                // transparent
                public static const material:MovieMaterial = new MovieMaterial(new materialData, true);
                material.smooth = true;
                // material.doubleSided = true;
                material.updateBitmap();
            
	        public static var materialList:MaterialsList = new MaterialsList();
                
                // the second argument is the ID that needs to
                // correspond to the id of the material inside the .dae file
                // in the mesh information (so in other words the library_materials bit of the collada is discarded)
	        // materialList.addMaterial(material, "uv_face_cube_jpg-Material002"); 
	                                               
	        materialList.addMaterial(material, "matsku");
                protected var model:DisplayObject3D = null;
		
                // these need to correspond to the sections of the building so that they can be turned transparent
                // depending on camera position
                public var northWalls:Array = null;
                public var southWalls:Array = null;
                public var eastWalls:Array = null;
                public var westWalls:Array = null;
                public var ceilings:Array = null;
                public var floors:Array = null;
                
        
                public function Model() {
                        super(meshXML, materialList);
                        // collect important bits
                        // DisplayObject3D
                        southWalls = loadChildrenWithPrefix('south_wall');
                        northWalls = loadChildrenWithPrefix('north_wall');
                        eastWalls = loadChildrenWithPrefix('east_wall');
                        westWalls = loadChildrenWithPrefix('west_wall');
                        ceilings = loadChildrenWithPrefix('ceiling');
                        floors = loadChildrenWithPrefix('floor');
                        // getChildByName('elokuvastudio').getChildByName('north_wall');
                        // southWall.useOwnContainer = true;
                }
                
                public function draw():void {
                }
                
                public function loadChildrenWithPrefix(prefix:string):Array {
                        var a:Array = new Array();
                        recurseChildren(prefix, this, a);
                        return a;
                }
                
                private function recurseChildren(prefix:string, curParent, curArray:Array) {
                       for(i in children) {
                                var child = children[i];
                                trace(i + ": " + child);
                                if( i.substr(0, prefix.length) == prefix) {
                                        trace("match!");
                                        curArray.push(child);
                                        child.useOwnContainer = true;
                                }
                                curArray = curArray.concat( recurseChildren(prefix, child, curArray) );
                        }
                        return curArray;
                }
                
                public function setTransparentWall(wallElems:Array):void {
                        for(i in southWalls) { southWalls[i].alpha = 1.0; }
                        for(i in northWalls) { northWalls[i].alpha = 1.0; }
                        for(i in westWalls)  { westWalls[i].alpha = 1.0; }
                        for(i in eastWalls)  { eastWalls[i].alpha = 1.0; }
                        for(i in wallElems) { wallElems[i].alpha = 0.1; }
                }
                
                public function onCameraUp():void {
                        for(i in floors) { floors[i].alpha = 1.0; }
                        for(i in ceilings) { ceilings[i].alpha = 0.1; }
                }
                
                public function onCameraDown():void {
                        for(i in ceilings) { ceilings[i].alpha = 1.0; }
                        for(i in floors)   { floors[i].alpha = 0.1; }
                }

	}
}
