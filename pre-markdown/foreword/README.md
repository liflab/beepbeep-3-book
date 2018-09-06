Foreword
========

This foreword could also be called: *Sylvain, why did you write this book?*

Short answer: because I am *lazy*.

In the research lab where I work at Université du Québec à Chicoutimi (the *Laboratoire d'informatique formelle* or LIF), I have been developing the system described in this book for a couple of years. It started as a small library with just a few hundred lines of code, which was progressively restructured, extended, refactored, split and merged, to become the relatively stable product that we shall detail in the next pages: the **BeepBeep** event stream processing engine.

Over the years, many of my students had their hands on BeepBeep as part of their research projects. Some of them were undergrads staying for a Summer internship, and who were asked to develop a specific extension to the existing software. Others were Masters or even PhD students, who provided a deeper contribution to the system, or tested it extensively in R&D projects with industry.

Every time a new colleague would join our team (whether this was a new student, or a new faculty member), the same problem would happen: he or she needed to be taught what BeepBeep was, how it worked, and what it could and couldn't do. Most people in the group had bits and pieces of that information, but for the most part, *I* was the one with an eye on everything. So the task of tutoring newcomers on the nuts and bolts of the system would generally fall on me.

Make no mistake: I love teaching, and I love talking about BeepBeep. However, I realized over time that this permanent one-on-one coaching could not be sustainable for long. First was the question of time: like all good university professors, I tend to put more on my plate than I can actually manage, which leaves little room for regular private lessons. But most importantly, I soon acknowledged that I lacked good *teaching material* about BeepBeep.

Sure, we wrote about half-a-dozen scientific papers about the system in the past four or five years. In the beginning, I assumed I could simply staple these papers together, give them to any BeepBeep neophyte and call it a day. In retrospect, I can see why this doesn't work: a research paper is meant for a technical audience of knowledgeable people, and is very narrow in scope. On top of that, it is reviewed by a panel of grumpy referees who will reject it at the slightest weakness (I sometimes fall in that trap myself when I review other people's work!). As a consequence, in the Computer Science world of 2018, a publication is crafted to act as one half of an arm wrestling game between authors and reviewers --hardly the layman's gentle introduction to some topic.

I came to admit that without a thorough and well-structured tutorial, BeepBeep would still rely on oral tradition in order to be understood by users in ten years (assuming people are still interested in BeepBeep in ten years). People from outside our lab would probably never know it exists, and if they did, would probably never take the time to decrypt the research papers by themselves, let alone make their minds about whether it could be useful to them or not. I had to work on a "BeepBeep book", if I wanted this book to work for me afterwards. Then I could afford to be lazy.

The rest is simple: I sat down and started typing. This simple process had a positive impact on the system itself: it made me fix some inconsistencies in naming conventions, forced me to standardize and extend the pool of graphical symbols I was already using informally, and overall, added a layer of polish on the library to make it presentable to the outside world. What started as a small documentation file ended up as a complete book, which in its current version contains:

- **119** different code examples, for a total of 3,800 lines of Java
- **128** colour illustrations
- **29** exercises across all chapters

This took a considerable amount of time, but I am glad I did it. I wish all my research projects were given that much care, and that more researchers in the field of Computer Science pushed their prototypes closer to the end users.

## Book Toolchain

This book exists in two versions:

- An "e-book" (or PDF) version, published by the Presses de l'Université du Québec (PUQ) and accessible through their website: `https://www.puq.ca`. This book is published under an *open access* policy; it has been given an ISBN and has all the features of a "real" paper book: reviewing, editing, copyright registration. However, it is only accessible electronically --free of charge. **If you want to cite BeepBeep's book in your work, please cite this version.**
- An online, interactive version, accessible on <!--\index{GitBook} GitBook-->GitBook<!--/i-->: `https://liflab.gitbook.io`. This version is viewable in a web browser; contrary to the PUQ book, it *will* be updated in the future to match the evolution of the library. However, I shall stress that the PUQ have nothing to do with the contents of that version.

For those who are curious, both the PDF and the GitBook versions, although they look different, are generated from the same source files, written in the Markdown format. (Maintaining two versions in parallel would be a nightmare.) To this end, I set up a simple toolchain, made of a bunch of PHP scripts and Java programs, that can convert the original files into a directory structure suitable for GitBook. What is more, all the source code examples are not hard-coded into the text, but are rather dynamically inserted from references to markers in the actual source code repository --making it much easier to keep the code and the book in sync.

However, if you want to turn that same documentation into something that's printable, GitBook it not your friend. The file it generates feels like (and as a matter of fact, actually is) a bunch of web pages printed to PDF from a web browser and smacked one after the other. This is acceptable for e-readers or on-screen viewing, but gives a rather sub-standard look for a printed document. Even bare-bones features, such as page numbers and a table of contents, are absent from the generated document. Besides, apart from a few stylesheet tweaks, you have absolutely no control over the appearance of the resulting PDF. Needless to say, this output cannot be used as the basis for a professional-looking printed book.

This is why I wrote some more scripts to generate, from the same sources, another directory structure where the files are converted to LaTeX, thanks to the [Pandoc](https://pandoc.org) <!--\index{Pandoc} conversion-->conversion<!--/i--> software. In such a way, a decent stylesheet can be applied to the book, which also benefits from all the usual LaTeX goodies: an index, a table of contents, vector graphics instead of GitBook's bitmaps, spotless typography, and so on.

Those interested may have a look at the GitHub repository containing the basic structure of a GitBook/LaTeX hybrid document at: `https://github.com/sylvainhalle/gitbook-latex`.

## Long-term preservation of resources

<!--\index{Berners-Lee, Tim} Tim Berners-Lee-->Tim Berners-Lee<!--/i-->, inventor of the web, once said: "[Cool URIs don't change](https://www.w3.org/Provider/Style/URI.html)". Unfortunately, online resources have shown an unfortunate tendency of moving around, and sometimes vanish.

When I started my undergraduate studies (almost 20 years ago), *SourceForge* was the platform where all cool projects were developed. Today, the site is the shadow of itself, and seems to stay online to host the latest version of legacy software projects. A few years ago, *Google Code* was the new repository to hang around; yet in 2015, Google announced it would close down the platform, leading to the disappearance of thousands of software projects.

BeepBeep is currently hosted on GitHub, a popular and dynamic software repository. But GitHub could have the same fate as the repositories that came and went before it. If you read this book, some time in the future, you may not be able to find the resources at the URLs they are supposed to be.

In such a case, your last resort will probably be to look at the <!--\index{Software Heritage (platform)} \textit{Software Heritage}-->*Software Heritage*<!--/i--> platform (`https://softwareheritage.org`). The goal of this UNESCO-backed project is to "collect all publicly available software in source code [and] replicate it massively to ensure its preservation". Among other things, Software Heritage indexes and backs up all well-known code repositories. BeepBeep is there, as is the source code for this book. Just search for "beepbeep" and you shall find multiple repositories and forks of the original piece of software.

## Acknowledgements

I end this foreword by saying "thank you" to a few people who helped me in the development of BeepBeep in general, and of this book in particular:

- All the students who worked *with* or *on* BeepBeep throughout the years: Asma,
Aouatef, Armand, Belkacem, Corentin, Dominic, Eva, Jérôme, Kim, Kun, Luis, Massiva, Mohamed (R and Y), Omar, Paul, Pierre-Louis, Quentin, Rémi, Simon, Sébastien, Stéphanie, Théo and Valentin. They have shown commendable patience for working with an unfinished, unpolished tool, and for being the first beta-testers of just about everything there is inside this system.
- My colleagues at LIF, professors Sébastien Gaboury and Raphaël Khoury, who have been willing to use BeepBeep in some of their own projects and gave it its first big shakedown.
- Ginette Tremblay, for carefully reviewing the manuscript and correcting my English slang.
- The staff at Presses de l'Université du Québec, for being open to this unusual book project and accept to publish it in their *open access* collection.
- My family and friends, who supported me while I was writing this book --sometimes at the most inopportune moments.

I hope that you will find this book both instructive and enjoyable to read!

<!-- Leave the two spaces at the end of the following lines. They tell Pandoc
     to break lines-->
  
  
Sylvain Hallé  
Jonquière, Canada  
8/16/2018


<!-- :wrap=soft: -->