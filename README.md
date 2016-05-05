# sflow-parser

This script serves as a basic CLI parser for sflowtool to provide per interface rates and flow calcuation. It was originally made just to verify the sflow counter data was in fact correct.

##Options

### Required

    --type              [cntr|flow]

###Optional

    --filter            Filter flow (eg. device:10.0.0.1,interface:19)
    --json              Output data in JSON format (after filtering)
    --dump              Output data in JSON format (before filtering)

##Examples

###Filtering counters by interface and device

    sflowtool -p 6341 -l | php /scripts/sflow.php --type cntr --filter device:10.0.0.1,interface:19

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

###Filtering flows by device and output in JSON

    sflowtool -p 6341 -l | php /scripts/sflow.php --type cntr --filter device:10.0.0.1 --json

    Collecting flows, polling interval 2s
    {
        "device": "10.0.0.1",
        "input_port": "0",
        "output_port": "542",
        "src_mac": "54e053040471",
        "dst_mac": "902950974412",
        "ethernet_type": "0x0800",
        "in_vlan": "0",
        "out_vlan": "9",
        "src_ip": "x.x.x.x",
        "dst_ip": "x.x.x.x",
        "ip_protocol": "6",
        "ip_tos": "0x00",
        "ip_ttl": "55",
        "src_port_or_icmp_type": "51555",
        "dst_port_or_icmp_code": "443",
        "tcp_flags": "0x10",
        "packet_size": "68",
        "ip_size": "46",
        "sampling_rate": "1000"
    }

###Filtering flows by negated vlan and output in JSON

    sflowtool -p 6341 -l | php /scripts/sflow.php --type cntr --filter 'in_vlan:!10' --json

    Collecting flows, polling interval 2s
    {
        "device": "10.0.0.1",
        "input_port": "0",
        "output_port": "542",
        "src_mac": "54e053040471",
        "dst_mac": "902950974412",
        "ethernet_type": "0x0800",
        "in_vlan": "0",
        "out_vlan": "9",
        "src_ip": "x.x.x.x",
        "dst_ip": "x.x.x.x",
        "ip_protocol": "6",
        "ip_tos": "0x00",
        "ip_ttl": "55",
        "src_port_or_icmp_type": "51555",
        "dst_port_or_icmp_code": "443",
        "tcp_flags": "0x10",
        "packet_size": "68",
        "ip_size": "46",
        "sampling_rate": "1000"
    }

###Filtering flows by negated device regex and regex input port and output in JSON

    sflowtool -p 6341 -l | php /scripts/sflow.php --type cntr --filter 'device:!.*14,input_port:^55' --json

    Collecting flows, polling interval 2s
    {
        "device": "10.0.0.1",
        "input_port": "551",
        "output_port": "542",
        "src_mac": "54e053040471",
        "dst_mac": "902950974412",
        "ethernet_type": "0x0800",
        "in_vlan": "0",
        "out_vlan": "9",
        "src_ip": "x.x.x.x",
        "dst_ip": "x.x.x.x",
        "ip_protocol": "6",
        "ip_tos": "0x00",
        "ip_ttl": "55",
        "src_port_or_icmp_type": "51555",
        "dst_port_or_icmp_code": "443",
        "tcp_flags": "0x10",
        "packet_size": "68",
        "ip_size": "46",
        "sampling_rate": "1000"
    }
