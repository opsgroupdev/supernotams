<?php

use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;

it('asks a question using gpt4', function () {
    try {
        $setupSystem = 'You are a php and laravel expert';
        $question = <<< 'EOL'
I have a SOAP response from a soap service that i want to decode and extract data from using php and preferably native methods/functions of php.

How would I extract the details from the TAIMSCrewRostItm items without using the xpath notation.

Here is an example of the soap response:
<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                   xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
    <SOAP-ENV:Body SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
                   xmlns:NS1="urn:AIMSWebServiceIntf-IAIMSWebService" xmlns:NS2="urn:CrwSrv">
        <NS1:CrewMemberRosterDetailsForPeriodResponse>
            <return href="#1"/>
        </NS1:CrewMemberRosterDetailsForPeriodResponse>
        <NS2:TAIMSCrewRostDetailList id="1" xsi:type="NS2:TAIMSCrewRostDetailList">
            <CrewRostList xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="NS2:TAIMSCrewRostItm[9]">
                <item href="#2"/>
                <item href="#3"/>
                <item href="#4"/>
                <item href="#5"/>
                <item href="#6"/>
                <item href="#7"/>
                <item href="#8"/>
                <item href="#9"/>
                <item href="#10"/>
            </CrewRostList>
            <CrewRostCount xsi:type="xsd:int">9</CrewRostCount>
            <ErrorExplanation xsi:type="xsd:string"></ErrorExplanation>
        </NS2:TAIMSCrewRostDetailList>
        <NS2:TAIMSCrewRostItm id="2" xsi:type="NS2:TAIMSCrewRostItm">
            <Day xsi:type="xsd:string">11/03/2024</Day>
            <Carrier xsi:type="xsd:string"></Carrier>
            <Flt xsi:type="xsd:string">GL</Flt>
            <Legcd xsi:type="xsd:string"></Legcd>
            <Dep xsi:type="xsd:string">DUB</Dep>
            <Arr xsi:type="xsd:string"></Arr>
            <CrewBase xsi:type="xsd:string"></CrewBase>
            <STD xsi:type="xsd:string"></STD>
            <STDLocal xsi:type="xsd:string"></STDLocal>
            <STDBase xsi:type="xsd:string"></STDBase>
            <STA xsi:type="xsd:string"></STA>
            <STALocal xsi:type="xsd:string"></STALocal>
            <STABase xsi:type="xsd:string"></STABase>
            <ATD xsi:type="xsd:string"></ATD>
            <ATDLocal xsi:type="xsd:string"></ATDLocal>
            <ATDBase xsi:type="xsd:string"></ATDBase>
            <ATA xsi:type="xsd:string"></ATA>
            <ATALocal xsi:type="xsd:string"></ATALocal>
            <ATABase xsi:type="xsd:string"></ATABase>
            <GDBEG xsi:type="xsd:string"></GDBEG>
            <GDBEGLocal xsi:type="xsd:string"></GDBEGLocal>
            <GDBEGBase xsi:type="xsd:string"></GDBEGBase>
            <GDEND xsi:type="xsd:string"></GDEND>
            <GDENDLocal xsi:type="xsd:string"></GDENDLocal>
            <GDENDBase xsi:type="xsd:string"></GDENDBase>
            <PAX xsi:type="xsd:string"></PAX>
            <CROUTE xsi:type="xsd:string"></CROUTE>
            <CDATE xsi:type="xsd:string"></CDATE>
            <STDLocalDay xsi:type="xsd:string"></STDLocalDay>
            <STDBaseDay xsi:type="xsd:string"></STDBaseDay>
            <STADay xsi:type="xsd:string"></STADay>
            <STALocalDay xsi:type="xsd:string"></STALocalDay>
            <STABaseDay xsi:type="xsd:string"></STABaseDay>
            <ATDDay xsi:type="xsd:string"></ATDDay>
            <ATDBaseDay xsi:type="xsd:string"></ATDBaseDay>
            <ATDLocalDay xsi:type="xsd:string"></ATDLocalDay>
            <ATADay xsi:type="xsd:string"></ATADay>
            <ATABaseDay xsi:type="xsd:string"></ATABaseDay>
            <ATALocalday xsi:type="xsd:string"></ATALocalday>
            <GDBEG_Day xsi:type="xsd:string"></GDBEG_Day>
            <GDBEGBaseDay xsi:type="xsd:string"></GDBEGBaseDay>
            <GDENDLocalDay xsi:type="xsd:string"></GDENDLocalDay>
            <GDEND_Day xsi:type="xsd:string"></GDEND_Day>
            <GDENDBaseDay xsi:type="xsd:string"></GDENDBaseDay>
            <GDBEGLocalDay xsi:type="xsd:string"></GDBEGLocalDay>
        </NS2:TAIMSCrewRostItm>
        <NS2:TAIMSCrewRostItm id="3" xsi:type="NS2:TAIMSCrewRostItm">
            <Day xsi:type="xsd:string">12/03/2024</Day>
            <Carrier xsi:type="xsd:string"></Carrier>
            <Flt xsi:type="xsd:string">GL</Flt>
            <Legcd xsi:type="xsd:string"></Legcd>
            <Dep xsi:type="xsd:string">DUB</Dep>
            <Arr xsi:type="xsd:string"></Arr>
            <CrewBase xsi:type="xsd:string"></CrewBase>
            <STD xsi:type="xsd:string"></STD>
            <STDLocal xsi:type="xsd:string"></STDLocal>
            <STDBase xsi:type="xsd:string"></STDBase>
            <STA xsi:type="xsd:string"></STA>
            <STALocal xsi:type="xsd:string"></STALocal>
            <STABase xsi:type="xsd:string"></STABase>
            <ATD xsi:type="xsd:string"></ATD>
            <ATDLocal xsi:type="xsd:string"></ATDLocal>
            <ATDBase xsi:type="xsd:string"></ATDBase>
            <ATA xsi:type="xsd:string"></ATA>
            <ATALocal xsi:type="xsd:string"></ATALocal>
            <ATABase xsi:type="xsd:string"></ATABase>
            <GDBEG xsi:type="xsd:string"></GDBEG>
            <GDBEGLocal xsi:type="xsd:string"></GDBEGLocal>
            <GDBEGBase xsi:type="xsd:string"></GDBEGBase>
            <GDEND xsi:type="xsd:string"></GDEND>
            <GDENDLocal xsi:type="xsd:string"></GDENDLocal>
            <GDENDBase xsi:type="xsd:string"></GDENDBase>
            <PAX xsi:type="xsd:string"></PAX>
            <CROUTE xsi:type="xsd:string"></CROUTE>
            <CDATE xsi:type="xsd:string"></CDATE>
            <STDLocalDay xsi:type="xsd:string"></STDLocalDay>
            <STDBaseDay xsi:type="xsd:string"></STDBaseDay>
            <STADay xsi:type="xsd:string"></STADay>
            <STALocalDay xsi:type="xsd:string"></STALocalDay>
            <STABaseDay xsi:type="xsd:string"></STABaseDay>
            <ATDDay xsi:type="xsd:string"></ATDDay>
            <ATDBaseDay xsi:type="xsd:string"></ATDBaseDay>
            <ATDLocalDay xsi:type="xsd:string"></ATDLocalDay>
            <ATADay xsi:type="xsd:string"></ATADay>
            <ATABaseDay xsi:type="xsd:string"></ATABaseDay>
            <ATALocalday xsi:type="xsd:string"></ATALocalday>
            <GDBEG_Day xsi:type="xsd:string"></GDBEG_Day>
            <GDBEGBaseDay xsi:type="xsd:string"></GDBEGBaseDay>
            <GDENDLocalDay xsi:type="xsd:string"></GDENDLocalDay>
            <GDEND_Day xsi:type="xsd:string"></GDEND_Day>
            <GDENDBaseDay xsi:type="xsd:string"></GDENDBaseDay>
            <GDBEGLocalDay xsi:type="xsd:string"></GDBEGLocalDay>
        </NS2:TAIMSCrewRostItm>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOL;

        $response = OpenAI::chat()
            ->create([
                'model'    => 'gpt-4', //gpt-4, gpt-4-turbo-preview, gpt-3.5-turbo
                'messages' => [
                    ['role' => 'system', 'content' => $setupSystem],
                    ['role' => 'user', 'content' => $question],
                ],
            ]);
    } catch (TransporterException $exception) {
        Log::error("{$exception->getMessage()}");
    } catch (Throwable $exception) {
        Log::error("{$exception->getMessage()}");
    }

    $answer = $response->choices[0]->message;
    Log::info('Chat GPT', [$response->toArray()]);
})
    ->skip();
