This script serves as a basic CLI parser for sflowtool to provide per interface rates and flow calcuation. It was originally made just to verify the sflow counter data was in fact correct.

##Options

### Required

    --type              [cntr|flow]

###Optional

    --device            IP address of sflow sender
    --interface         Interface name as reported by sflow (often the SNMP idx)

##Examples

###Filtering counters by interface and device

    sflowtool -p 6341 -l | php /scripts/sflow.php --type cntr --device 10.0.0.1 --interface 19


