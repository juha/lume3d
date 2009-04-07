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
	        [Embed (source="../media/simple_cube.dae", mimeType="application/octet-stream")]
	        public static const meshData:Class;
	        
	        public static const meshXML:XML = function():XML { 
	                      var byteArray:ByteArray = new meshData() as ByteArray; 
	                      return new XML(byteArray.readUTFBytes(byteArray.length));
	              }();
	        
	        [Embed (source="../media/uv_face_cube.png")]
	        public static const materialData:Class;
	        public static const material:MovieMaterial = new MovieMaterial(new materialData);
	        public static var materialList:MaterialsList = new MaterialsList();
	        materialList.addMaterial(material, "uv_face_cube_png"); // the second argument is the ID that needs to 
	                                                // correspond to the id of the material inside the .dae file
	        
    
    
		protected var model:DisplayObject3D = null;
		
                // public var yaw:Number = 0;
                // public var roll:Number = 0;
                // public var pitch:Number = 0;
                
                public function Model() {
                        super(meshXML, materialList);
                }
                
		public function draw():void {
			// this.yaw(yaw);
			// this.roll(roll);
			// this.pitch(pitch);
		}

	}
}