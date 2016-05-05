# sflow-parser

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

    Collecting flows, polling interval 5s
    IP           Interface                  In                         Out
    10.0.0.1     19                3.21 Mb/s     3 kpps      96.33 Mb/s     9 kpps
    10.0.0.1     19                4.95 Mb/s     4 kpps     143.25 Mb/s    13 kpps
    10.0.0.1     19                4.86 Mb/s     4 kpps     134.12 Mb/s    13 kpps
    10.0.0.1     19                3.65 Mb/s     3 kpps     102.77 Mb/s     9 kpps
    10.0.0.1     19                3.26 Mb/s     3 kpps      88.87 Mb/s     8 kpps
    10.0.0.1     19                4.52 Mb/s     4 kpps     117.22 Mb/s    11 kpps
    10.0.0.1     19                3.22 Mb/s     2 kpps     108.80 Mb/s    10 kpps
    10.0.0.1     19                3.45 Mb/s     2 kpps      94.14 Mb/s     9 kpps


