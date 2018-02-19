Introduction
============

Many computing problems can be viewed as the evaluation of queries over data sources called *streams*. A stream is made of discrete data elements, called *events*; these can be as simple as a single number, or as complex as a special data structure with a large number of fields, a piece of text, or even a picture. 

Streams can produce these events at rates that vary widely: a sensor can emit a temperature reading once per minute, while a financial transaction system may log a new entry thousands of times per second.

## What is BeepBeep?

In this book, you will learn how to use the BeepBeep event stream query engine to perform various tasks over event streams of different nature.

BeepBeep has a few interesting features that distinguish it from other software systems based on events.

- It is **lightweight**. The core of BeepBeep is a stand-alone Java library that weighs less than 200 kilobytes (yes, that's *kilobytes*). BeepBeep also has low memory requirements; typically, as long as a Java virtual machine is available on a platform, BeepBeep can be made to run on it. It has been used in various environments, ranging from server clusters to smartphones and small devices such as the Raspberry Pi.
- It requires **zero configuration**. To start using BeepBeep, one simply needs to download the library and use the classes it provides in any Java program. Writing a working chain of processors (the basic computing units in BeepBeep) can be done in a few lines of code.
- It **does not force you to use a query language**. Many other event stream processing systems require you to write queries in some made up language that vaguely looks like SQL. In contrast, BeepBeep allows you to create, configure and pipe processor objects directly. As a result, the computation that is being executed is very close to your own mental model of what's happening. (And if you do want to use a query language, it also allows you to create your own.)
- It is **modular**. Apart from its small core of basic processors and functions, all other features of BeepBeep are bundled into a large number of optional plug-ins called *palettes*. This is in contrast with many other systems that attempt to provide a huge, monolithic, one-size-fits-all set of functionalities. In BeepBeep, you only use the palettes you need, resulting in a system that carries far less dead code.
- It is **versatile**. There are palettes to read Excel spreadsheets, parse Apache server logs, perform data mining, calculate statistics, analyze network packets, draw plots, and more. Among the weirdest palettes developed for BeepBeep, one even allows two smartphones to exchange data streams using their onboard camera and QR codes. As long as you can model a problem as a form of computation over streams, there is probably a way to do it with BeepBeep.
- It is heavily **customizable**. In case none of the existing palettes suit your needs, you can easily create your own processors, functions and events --typically in just a few lines of code. These custom-made objects can interact with all the others, meaning you only need to code what is missing, and not reinvent the wheel.

## What BeepBeep is not

Although BeepBeep has a host of interesting features, it is not a panacea. There are other things for which it is not so good, or that have been purposefully excluded from its design.

- It is not a **distributed computing environment**. Although you can easily pass events around across machines using special network palettes, this is a far cry from what elaborate fault-tolerant publish-subscribe dispatching systems can provide you.
- It is not a **high-performance computing environment**. There are lots of things you can do reasonably well in BeepBeep, and numerous use cases for which it is more than fast enough. However, if your target throughput is expressed in exabytes per second, chances are BeepBeep will be too sluggish to your taste.
