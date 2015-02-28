#!/usr/bin/perl
use strict;
use warnings;
no warnings 'experimental';
my $dir = "./AP/";
opendir(DH, $dir) or die $!;
my @files = readdir(DH);
closedir(DH);
foreach my $file (@files)
{
# skip . .. and README
next if($file =~ /^\.$/);
next if($file =~ /^\.\.$/);
next if($file =~ /^README$/);
next if($file =~ /^DOCS$/);
print "Traitement de : $file\n";
# $file is the file used on this iteration of the loop
# Put all the file in a single string
my $all_docs = "";
open IN, $dir.$file or die $!;
while (<IN>) {
$all_docs .= $_;
}
close IN;
# Cut the docs
my @docs = split /<\/DOC>/, $all_docs;
foreach my $doc (@docs) {
my $doc_name = "";
if ($doc =~ /<DOCNO> (.+) <\/DOCNO>/) {
$doc_name = $1;
} else {
next;
}
my $doc_dir = "DOCS/";
open OUT, '>', $dir.$doc_dir.$doc_name or die $!;
print OUT $doc;
print OUT "</DOC>\n";
close OUT;
}
}
