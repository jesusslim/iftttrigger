# iftttrigger
trigger the events of ifttt maker

## install

	composer require jesusslim/iftttrigger
	
## usage

    $ift = new MakerTrigger('yourkey');
    $ift->fire('test2',['value1' => 'foo']);

## get your key of ifttt maker

    https://ifttt.com/maker_webhooks