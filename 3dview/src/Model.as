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
                public var northWall:DisplayObject3D = null;
                public var southWall:DisplayObject3D = null;
                public var eastWall:DisplayObject3D = null;
                public var westWall:DisplayObject3D = null;
                public var ceiling:DisplayObject3D = null;
                public var floor:DisplayObject3D = null;
                
        
                public function Model() {
                        super(meshXML, materialList);
                        // collect important bits
                        southWall = getChildByName('elokuvastudio').getChildByName('south_wall');
                        northWall = getChildByName('elokuvastudio').getChildByName('north_wall');
                        westWall = getChildByName('elokuvastudio').getChildByName('north_wall');
                        eastWall = getChildByName('elokuvastudio').getChildByName('north_wall');
                        ceiling = getChildByName('elokuvastudio').getChildByName('north_wall');
                        floor = getChildByName('elokuvastudio').getChildByName('north_wall');
                        
                        southWall.useOwnContainer = true;
                        northWall.useOwnContainer = true;
                        westWall.useOwnContainer = true;
                        eastWall.useOwnContainer = true;
                        ceiling.useOwnContainer = true;
                        floor.useOwnContainer = true;
                }
                
                public function draw():void {
                }

	}
}
