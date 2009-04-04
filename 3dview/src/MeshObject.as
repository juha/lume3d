package
{
	import mx.core.Application;
	import org.papervision3d.materials.utils.MaterialsList;
	import org.papervision3d.objects.DisplayObject3D;
	import org.papervision3d.objects.parsers.Collada;

	public class MeshObject extends BaseObject
	{
		protected var model:DisplayObject3D = null;
		
		public function MeshObject()
		{
			
		}
		
		override public function shutdown():void
		{
			super.shutdown();
			Application.application.engineManager.defaultScene.removeChild(model);
			model = null;
		}
		
		public function startupModelObject(collada:XML, materials:MaterialsList):void
		{
			super.startupBaseObject();
			model = new Collada(collada, materials);
			Application.application.engineManager.defaultScene.addChild(model);
		}
		
		override public function enterFrame(dt:Number):void
		{
			model.yaw(25 * dt);
			model.roll(25 * dt);
			model.pitch(25 * dt);
		}

	}
}