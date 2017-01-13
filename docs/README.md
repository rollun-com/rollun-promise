#Promise  

----------


##Обзор
### Что это?
Эта библиотека максимально совместимая со стандартом ["Promises/A+/](https://promisesaplus.com/ "Promises/A+") и по интерфейсу похожа на реализацию [Guzzle Promises](https://github.com/guzzle/promises "Guzzle Promises").
### В чем "уникальность"?

- Система персистентна - все **Promise** хранятся в базе.  

- Ничего не "демонизируется" например можно построить приложение, которое, работая на виртуальном хостинге с ограничением на время работы скрипта, будет асинхронно взаимодействовать с "медленными" сторонними сервисами.

##Быстрый старт
**Promise** - это просто контейнер для хранения результата операции.  
Вы можете:  

- его создать:` $promise = new Promise();`  
- заполнить: ` $promise = $promise->resolve('foo');`  
- считать результат:` $resalt = $promise->wait();//'foo'`

Еще можно сказать, что сделать с результатом, когда им будет заполнен контейнер. Метод
     
	public function then(callable $onFulfilled = null, callable $onRejected =null);
позволяет задать колбеки для обработки результата или исключения. Пример:

	use rollun\promise\Promise\Promise;

	$masterPromise = new Promise;
	$slavePromise = $masterPromise->then(function($val) {
	    var_dump('Hello ' . $val);
	});
	$masterPromise->resolve('World');	//string 'Hello World' (length=11)


##Статусы
###Статусы согласно стандарту Promise A+
Согласно стандарту **Promise** может находится в одном из трех статусов:  
  
- 'pending';  
- 'fulfilled';  
- 'rejected';

В большинстве случаев для работы с **Promise** этого достаточно, но давайте рассмотрим, что проиходит, когда вы "кладете" в **Promise** в качестве результата
другой **Promise**. По стандарту, первый зависимый (slave) **Promise** принимает статус переданного (master) **Promise**.  
Если  **masterPromise** определен ('fulfilled' или 'rejected'), то **slavePromise** будет заполнен значением из **masterPromise** и перейдет в соответствующий статус.
Но если **masterPromise** в статусе 'pending', то **slavePromise** будет ждать, пока **masterPromise** "определится". 

###Четвертый статус - 'dependent'
Так в каком статусе находится **slavePromise**? Формально - 'pending', но при этом, он заблокирован для изменений извне.
Повлиять на его статус может только изменение статуса **masterPromise**. Попытки вызвать у **slavePromise** метод 
reject($reason) или resolve($value) вызовут исключение.   
Таким образом мы имеем четвертое состояние (статус) **Promise**

 - 'dependent'  

, который является частным случаем статуса 'pending'.


##Интерфейс  

### Методод getState()
Метод `getState(true)` или просто `getState()` возвращает один из трех статусов.
Вызов `getState(false)` может вернуть дополнительный статус 'dependent', который по умолчанию 
представлен статусом 'pending'.  

    public function getState($dependentAsPending = true);
Вызов метода `getState()` может вернуть одно из значений:

- 'pending';  
- 'fulfilled';  
- 'rejected';  

Вызов метода `getState(false)` может вернуть еще одно значение:

- 'dependent';

----------
### Метод reject($reason)
Вызов метода `reject($reason)` говорит о том, что попытка получить результат закончилась неудачей. Параметр `$reason` это либо Exception, либо сообщение (`string`), с которым будет сгенерирован `Exception`. Так же можно передать **Promise**.     
Если **Promise** 'fulfilled', то `Exception` в сообщении выведет значение переданного **Promise**.  
Если **Promise** 'rejected', то `Exception` будет взят из переданного **Promise**.  
Если **Promise** 'pending', то  в сообщении `Exception` будет "'Reason is pending promise. ID = promise__14756...**"


	public function reject($reason);
Переводит **Promise** из статуса 'pending' в 'rejected'.  
При вызове у **Promise** в статусе 'fulfilled' - бросает исключение `AlreadyFulfilledException`  
При вызове у **Promise** в статусе 'rejected' - бросает исключение `AlreadyRejectedException`, если 
аргумент $reason не эквивалентен ранее переданному значению.   
При вызове у **Promise** в статусе 'dependent' - бросает исключение `AlreadyResolvedException`, если 
аргумент $reason не является исключением типа `TimeIsOutException`.   

----------
### Метод resolve($value)
Передает в **Promise** результат. Параметром может быть скаляр, массив, объект, Exception или другой  **Promise**. Даже если пердан Exception, **Promise** перейдет в статус 'fulfilled', а не 'rejected'.

	public function resolve($value);
При вызове у **Promise** в статусе 'pending' переводит переводит **Promise** из статуса 'pending' в 'fulfilled'. Если в качестве результата передан другой **Promise** (master), то  из статуса 'pending' переводит **Promise** в статус 'dependent', если переданный **masterPromise** в статусе 'pending' или 'dependent'. Если же **masterPromise** в статусе 'rejected' или 'fulfilled', то **Promise** примет состояние  (статус м результат) как у **masterPromise**.
При вызове у **Promise** в статусе 'fulfilled' - бросает исключение `AlreadyFulfilledException`, если 
аргумент $value не эквивалентен ранее переданному значению.  
При вызове у **Promise** в статусе 'rejected' - бросает исключение `AlreadyRejectedException`.   
При вызове у **Promise** в статусе 'dependent' - бросает исключение `AlreadyResolvedException`. Однако если 
аргумент $value  является  **masterPromise**, то **Promise** примет состояние  (статус м результат) как у **masterPromise**.   

----------
### Метод wait($unwrap = true)



Метод `wait(false)` считать значение из **Promise** немедленно и без генерации исключений. Метод `wait(true)` - позволяет синхронно "разрешить" **Promise**.

	public function wait($unwrap = true);

####Вызов `wait(false)` возвращает:
При вызове у **Promise** в статусе 'fulfilled' - значение, которое было передано в
в методе `resolve()` ранеее.  
При вызове у **Promise** в статусе 'rejected' -  исключение которое было передано в
в методе `resolve()` ранее, либо (если в `resolve()` была передана строка) исключение `rollun\promise\Promise\Exception` с этой строкой в сообщении.    
При вызове у **Promise** в статусе 'dependent' или 'pending'- исключение `rollun\promise\Promise\Exception\TimeIsOutException`.  
####Вызов `wait(true)`:
При вызове у **Promise** в статусе 'fulfilled' - возвращает значение, которое было передано в
в методе `resolve()` ранеее.  
При вызове у **Promise** в статусе 'rejected' - бросает исключение которое было передано в
в методе `reject()` ранее, либо (если в `reject()` была передана строка) исключение `rollun\promise\Promise\Exception` с этой строкой в сообщении.    
При вызове у **Promise** в статусе 'dependent' или 'pending'- ждет 2 сек. пока "разрешится" **Promise**, проверяя его каждую секунду. Вместо `wait(true)` можно вызвать `wait(43)` и тогда 
ожидание составит 43 сек. Если за указанное время **Promise** не "разрешится", то будет брошено исключение `rollun\promise\Promise\Exception\TimeIsOutException`.


----------
### Метод  then(callable $onFulfilled = null, callable $onRejected = null);

Метод `then()` позволяет строить цепочки обработчиков для **Promise**.

    public function then(callable $onFulfilled = null, callable $onRejected = null);   

Метод возвращает "зависимый" **Promise** (slave). Если не передавать колбэки, то

        $masterPromise = new Promise;
        $slavePromise = $masterPromise->then();

эквивалентно   

        $masterPromise = new Promise;
        $slavePromise = new Promise;
		$slavePromise->resolve($masterPromise);

Иными словами, **slavePromise** принимает значение и статус **masterPromise**.  
Если колбэки в **slavePromise** передать, то они будут выполнены с результатом из **masterPromise**. При этом, если **masterPromise** был 'rejected', но $onRejected колбэк 
не выбросит исключение, то **slavePromise** получит статус 'fulfilled' и значение, возвращенное 
колбэком.




 
