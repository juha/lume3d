package
{
	import mx.core.Application;
	
	public class BaseObject
	{
		public function BaseObject()
		{
			
		}
		
		public function startupBaseObject():void
		{
			Application.application.engineManager.addBaseObject(this);
		}
		
		public function shutdown():void
		{
			Application.application.engineManager.removeBaseObject(this);
		} 
		
		public function enterFrame(dt:Number):void
		{
			
		}
	}
}