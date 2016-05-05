<?php

    class Sflow
    {

        const INTERVAL = 2;

        private $flows = [];
        private $counters = [];
        private $devices = [];

        private $collect = false;

        public function __construct()
        {
            $this->options = getopt('', [ 'device:', 'interface:', 'type:', 'json', 'dump' ]);

            if (!isset($this->options['type'])) {
                printf("Error: --type is required (flow|cntr)\n");
                exit(1);
            }

            $this->options['type'] = ucfirst($this->options['type']);
        }

        private function processCntr($values)
        {
            $keys = [ 'device', 'interface', 'type', 'speed', 'direction', 'status',
                      'in_octets', 'in_ucast_pkts', 'in_mcast_pkts', 'in_bcast_pkts', 'in_discards', 'in_errors', 'in_unknown_proto',
                      'out_octets', 'out_ucast_pkts', 'out_mcast_pkts', 'out_bcast_pkts', 'out_discards', 'out_errors', 'out_unknown_proto' ];

            $data = array_combine($keys, $values);
            $data['time'] = time();

            if (isset($this->options['dump']))
                return $this->json($data);

            if (!$this->filterDevice($data))
                return;

            if (!$this->filterInterface($data))
                return;

            if (isset($this->options['json']))
                return $this->json($data);

            $key = $data['device'].$data['interface'];

            if (!isset($this->counters[$key])) {
                $this->counters[$key] = $data;
                return;
            }

            $interval = $data['time'] - $this->counters[$key]['time'];

            $bps_in = ( $data['in_octets'] - $this->counters[$key]['in_octets'] ) / $interval * 8;
            $mbps_in = $bps_in / pow(1000, 2);
            $pps_in = ( $data['in_ucast_pkts'] - $this->counters[$key]['in_ucast_pkts'] + $data['in_mcast_pkts'] - $this->counters[$key]['in_mcast_pkts'] + $data['in_bcast_pkts'] - $this->counters[$key]['in_bcast_pkts']) / 1000 / $interval ;

            $bps_out = ( $data['out_octets'] - $this->counters[$key]['out_octets'] ) / $interval * 8;
            $mbps_out = $bps_out / pow(1000, 2);
            $pps_out = ( $data['out_ucast_pkts'] - $this->counters[$key]['out_ucast_pkts'] + $data['out_mcast_pkts'] - $this->counters[$key]['out_mcast_pkts'] + $data['out_bcast_pkts'] - $this->counters[$key]['out_bcast_pkts']) / 1000 / $interval;

            printf("%-12s %-15s %6.2f Mb/s %5d kpps\t%6.2f Mb/s %5d kpps\n",
                $data['device'],
                $data['interface'],
                $mbps_in,
                $pps_in,
                $mbps_out,
                $pps_out);

            $this->counters[$key] = $data;
            return;
        }

        private function parseFlow($values)
        {
            $keys = [ 'device', 'input_port', 'output_port', 'src_mac', 'dst_mac', 'ethernet_type', 'in_vlan', 'out_vlan', 'src_ip', 'dst_ip', 'ip_protocol', 'ip_tos', 'ip_ttl', 'src_port_or_icmp_type', 'dst_port_or_icmp_code', 'tcp_flags', 'packet_size', 'ip_size', 'sampling_rate' ];

            $data = array_combine($keys, $values);

            return $data;
        }

        private function json($data)
        {
            printf("%s\n", preg_replace('#^(\{|\})#m', '', json_encode($data, JSON_PRETTY_PRINT)));
        }

        private function processFlow($values)
        {
            $data = $this->parseFlow($values);

            if (isset($this->options['dump']))
                return $this->json($data);

            if (!isset($this->flows[$data['device']]))
                $this->flows[$data['device']] = [ 'bits' => [] ];

            if (!$this->filterDevice($data) || !$this->filterFlow($data) || !$this->filterPort($data))
                return;

            if (isset($this->options['json']))
                return $this->json($data);

            $mt = microtime(true);

            if (!isset($this->flows[$data['device']]['start']))
                $this->flows[$data['device']]['start'] = $mt;

            $this->flows[$data['device']]['bits'][] = $data['packet_size'] * $data['sampling_rate'] * 8;

            if ($mt - $this->flows[$data['device']]['start'] <= self::INTERVAL || count($this->flows[$data['device']]['bits']) <= 5)
                return;

            $interval = $mt - $this->flows[$data['device']]['start'];
            unset($this->flows[$data['device']]['start']);

            $flowps = count($this->flows[$data['device']]['bits']) / $interval;
            $bps = array_sum($this->flows[$data['device']]['bits']) / $interval;
            $mbps = $bps / pow(1024, 2);

            printf("%-12s %-15s %-15s %6.2f Mb/s %5d fps\n",
                $data['device'],
                $data['input_port'],
                $data['output_port'],
                $mbps,
                $flowps);


            $this->collect = true;

            $this->flows[$data['device']]['bits'] = [];
        }

        private function filterFlow($data)
        {
            if (preg_match('/^(172\.16|10)\.*/', $data['src_ip']) ||
                preg_match('/^(172\.16|10)\.*/', $data['dst_ip']))
                return false;
            return true;
        }

        private function filterDevice($data)
        {
            if (isset($this->options['device']) && $data['device'] != $this->options['device'])
                return false;
            return true;
        }

        private function filterInterface($data)
        {
            if (isset($this->options['interface']) && $data['interface'] != $this->options['interface'])
                return false;
            return true;
        }

        private function filterPort($data)
        {
            if (!isset($this->options['interface']))
                return true;

            $interfaceParts = explode(':', $this->options['interface']);

            if (count($interfaceParts) == 2 && $data[$interfaceParts[0].'put_port'] != $interfaceParts[1])
                return false;
            return true;
        }

        public function readInput()
        {
            printf("Collecting flows, polling interval %ss\n", self::INTERVAL);

            if (!isset($this->options['dump']) && !isset($this->options['json'])) {
                if ($this->options['type'] == 'Cntr')
                    printf("%-12s %-15s %13s %27s\n",
                        'IP', 'Interface', 'In', 'Out');
                else if ($this->options['type'] == 'Flow')
                    printf("%-12s %-15s %-15s\n",
                        'IP', 'Interface In', 'Interface Out');
            }

            while ($csv = fgetcsv(STDIN)) {
                $method = sprintf("process%s", ucfirst(strtolower($csv[0])));
                array_shift($csv);

                if ('process'.$this->options['type'] == $method)
                    $this->$method($csv);
            }
        }


    }

    $sflow = new Sflow;
    $sflow->readInput();

    exit;


