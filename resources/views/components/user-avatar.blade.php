 @props(['user','size'=>'w-12 h-12'])
 
 @if ($user->image)
     <img src="{{ $user->imageUrl() }}" alt="{{ $user->name }}" class="{{ $size }} rounded-full">
 @else
     <img src="https://pixy.org/src/31/315160.png" alt="Dummy avatar" class="w-12 h-12 rounded-full">
 @endif
