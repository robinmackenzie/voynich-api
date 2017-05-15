## Summary

Boris Viktorivich Sukhotin was a 20th century Russian mathematician. One of his algorithms is frequently discussed in relation to attempted decipherments of the so-called Voynich Manuscript (VMS). 

Oftentimes, in discussion of Sukhotin's algorithm with regard to the VMS, people will state that their analysis benefitted in some way from applying this algorithm, but most of the time it is not shown in either an repeatable, or interactive, manner how this was achieved. 

Hence this implementation of Sukhotin's Vowel/ Consonant identifying algorithm is presented as a Javascript class.

## Basic explanation of algorithm

Briefly, the algorithm can be explained through a number of steps:

1. Provide a sample text
2. Create a NxN matrix where N is the number of unique morphemes occuring in the sample text
3. Populate the matrix with adjacency frequencies e.g. for a morphemes M, count frequency of occurence of M in text to M-1 and M+1
4. Blank the main diagonal of the matrix i.e. from top-left to bottom-right in the matrix zero-out the values in those entries
5. First pass of row counts - for each row in table, sum frequencies across rows and store result
6. Identify highest non-zero row-count as a vowel excluding previously identified vowels
7. Update row counts by subtracting 2xN from each row where N is row-count of identified vowel
8. Repreat from step 6 until no non-zero row-counts remain i.e. all vowels identified

Notes and concerns on the above steps: 

1. Step 4 - It is really not clear why this step is required which is a concern as it appears a little arbitrary
2. Step 6 - It is not clear, in this implementation, how to handle the case where if in step 6 there are multiple rows with the same score. 

Unfortunately, I believe that Sukhotin is no longer with us in order to pose the questions from note 4 and 6. 

## Voynich research

Some research notes from the early 2000's are presented below.

Note that the two guys I reference are, or were, awesome contributors to this field of study and _do_ require lasting respect.

I've copied extracts to this repository for two reasons. Firstly, they were important in helping me develop the code. 
And, secondly, I have a small fear that one day the voynich.net archive won't be online anymore so I've simply copied the important (as I see it) detail here.
If you are interested in this topic it's worthwhile to study the archives of voynich.net

### Jacques Guy on Sukhotin
Please see entry in Wikipedia on Jacques Guy: https://en.wikipedia.org/wiki/Jacques_Guy

Jacques' write-up on voynich.net (http://voynich.net/Arch/2001/01/msg00108.html) is presented here for clarity:

#### CLASSIFYING LETTERS INTO VOWELS AND CONSONANTS

#### PRELIMINARY ASSUMPTIONS

1) The set of the written symbols the text is known:
   that is, the number and the distinctive features of the
   symbols are known.
2) The symbols are alphabetic, that is, they represent
   phonemes, not syllables or words.


#### SET OF ACCEPTABLE SOLUTIONS

An acceptable solution is a partition of the set of the
symbols of the text S into two disjoint subsets V and C
(vowels and consonants) the union of which is S.

If the alphabet of the language consists of n letters,
there are 2**n acceptable solutions.

#### OBJECTIVE FUNCTION

Texts written in an alphabetical system have the following
properties:

1) vowels tend to appear next to consonants rather than next
   to vowels.

2) consonants tend to appear next to vowels rather than next
   to consonants.

3) the most frequent phoneme in all languages known to date
   is a vowel (perhaps a consequence of the fact that
   all known languages have fewer vowels than consonants)

If languages did not have properties (1) and (2), the text of
property (2) would read: "cnsnntsooa tnde to ppraea nxte
to vwlsoe rhtrae thna nxte to cnsnntsooa" or "cnsnnts tnd
t ppr nxt t vwls rthr thn nxt t cnsnnts ooa e o aea e o oe ae
a e o ooa".

The objective function must then express either the frequency with
which members of set V occur next to members of set C, or the
frequency with which members of the same set occur contiguously.
In the former case the true solution is that for which the
function reaches a maximum, in the latter case that for which it
reaches a minimum.

#### DECIPHERMENT ALGORITHM

Consider an arbitrary solution from the set of acceptable solutions.
It is a partition of an alphabet into two subsets, vowels and
consonants.

Record in a 2x2 matrix the number of times each vowel of the
text occurs next to a vowel, and next to a consonant, and the
number of times each consonant occurs next to a consonant,
and next to a vowel:

                    Vowels     Consonants
                 .-----------------------.
     Vowels      |   f(V,V)  |  f(V,C)   |
                 |-----------+-----------|
     Consonants  |   f(C,V)  |  f(C,C)   |
                 '-----------------------'


The sum of the entries being constant, f(V,V)+f(C,C) is minimum
when f(C,V)+f(V,C) is maximum.

Computing all 2x2 matrices to choose the one for which the
objective function f(C,C)+f(V,V) is minimum is too expensive
computationally, since for a language with an alphabet of n
letters there are 2**n such matrices.

Record in the entries of an nxn matrix (n being the number
of the letters in the alphabet of the text) the number of times
f(i,j) letter i occurs next to letter j.

Fill its main diagonal with zeroes.

Let Sum(i) be the sum of the entries of the ith row.
Calculate Sum(i) for each row.

Let Cat(i) be the category (vowel or consonant) of letter i.
Set all letters to consonant, (i.e. for i:=1 to n do Cat(i):=consonant).

Repeat
    Select the letter m for which Sum(m) is maximum and Cat(i) is
    consonant.
    If Sum(m)> 0 then
       Set Cat(m) to vowel.
       Let Sum(i) = Sum(i)- f(i,m)*2 for all i's for which
       Cat(i) is consonant.
Until Sum(m)=0.

This algorithm was programmed for a BESM-2. The texts chosen for
the experiment contained 10,000 elements each. The results are
perfect for the Russian and Spanish texts.

E,a,o,i,u,y, and k were classified as vowels in the French text
and all the other letters as consonants. The letter k occurred
only six times, in abbreviations of foreign origin.

In the English text, the letters e,a,o,i,t,u,y were identified as
vowels, and the other letters as consonants. It is interesting that
t was incorrectly classified, probably because the combination th
following a consonant is extremely frequent, eg.: of the....
As such errors occur regularly, it is desirable to build algorithms
which correct the first, but do not change its results when they
are satisfactory, whilst improving them when they are not.

An improved algorithm  is based on the following idea: if we
have a string of five letters: x1, x2, x3, x4, x5 and if the
middle letter, x3, belongs to the vowel class, the majority
of the remaining letters x1, x2, x4, and x5 are likely to
belong to the other class (consonants) rather than to the same
(vowels). This improved algorithm, also programmed for computer,
gave satisfactory results.

As a general rule, it seems that the improved algorithm must
be designed so as to make the increase or decrease of the
objective function depend on a segment longer than is used
in the basic algorithm.

Let us mention in conclusion that, given a text coded into its
constituent morphemes we can expect the consonant/vowel algorithm
to analyze its components into notional morphemes (roots)
and auxiliary morphemes (such as, for instance, endings, articles,
prepositions, and conjunction).

### Jorges Stolfi on Sukhotin
Please see entry in Wikipedia on Jorge Stolfi: https://en.wikipedia.org/wiki/Jorge_Stolfi

Stolfi's comments on Sukhotin posted to voynich.net (view-source:http://voynich.net/Arch/2001/01/msg00112.html):

#### DIRECTED SIMILARITY

Say that two symbols X, Y are &quot;next-similar&quot; if they tend to be
followed by the same characters --- i.e., the probability distribution
of the next symbol after an X is similar to that of the next symbol
after any Y. Analogously, we can define &quot;prev-similar&quot; by looking at
the distributions of the characters right before occurrences of X and
of Y.  

In either definition, we may use statistics computed over the text
tokens, or over the list of distinct words (the &quot;lexicon&quot;), ignoring
the frequency of each word in the text. Experience seems to indicate
that the second approach provides more consistent results, at least
for Voynichese. (Which may be one point where Voynichese differs from
natural languages).  So, in what follows, symbol frequencies
are computed over the lexicon by default.

I don't know what is the right way to compare two distributions
quantitatively, but this issue doesn't seem to be terribly important
for what follows. So let's just assume that we have agreeed somehow on
numeric measures dnext(X, Y) and dprev(X, Y) of the distance between
the respective distributions, such that dnext(X, Y) = 0 when the
distribution after a X is identical to that after Y; and similarly for
dprev().

#### SIMILARITY CLASSES

We can use either similarity measure to group the symbols into a
pre-specified number k of &quot;classes&quot;, where all letters in the same
class are as similar as possible, and letters in different classes are
as dissimilar as possible. To find such classification, we could use
any clustering algorithm, or just tabulate the probabilities and do an
approximate classification by hand.

Sukhotin's algorithm can be seen as an instance of those clustering
algorithms, specialized for the case k = 2. Granted, its goal function
f() probably can't be expressed in terms of dnext() and dprev().
However, to a very rough approximation, we can say that Sukhotin's
procedure tries to optimize some symmetric mixture of the two. That
is, for most languages, two symbols will end up in the same Sukhotin
class if their next- and previous-symbol distributions, taken
together, are sufficiently similar.

The justification for Sukhotin's algorithm, viewed in this light, is
that we expect the discrepancies dnext() and dprev() to be much larger
between a vowel and a consonant than between two vowels or two
consonants --- if not for all languages, at least for vowel-rich
ones like Spanish and Italian. In particular, for two vowels X and Y,
the measure dnext(X, Y) will be lower than normal because both will
tend to be be followed by the same subset fo letters (the
consonsants), and ditto for dprev(X, Y). Therefore, it should not
matter much whether we use dnext, dprev, or some combination of the
two: the strongest split should be the V-C one in any case.

#### VOYNICHESE SYMBOL CLASSES ARE WEIRD

Well, that isn't the case of Voynichese, at least not with the
standard alphabets. The first problem is that, with either metric, the
widest splits occur between classes that are too small to be vowels,
or are unlikely for other reasons. For instance, in the dnext()
metric, the EVA letters { &lt;y&gt;, &lt;n&gt;, &lt;m&gt; } are very similar, and very
different from the rest; however, that is simply because they
almost always occur in word-final position, so that the next
&quot;character&quot; is almost always a space. That is not how vowels (or
consonants, for that matter) behave in most European languages. 
As another example, the four gallows { &lt;k&gt; &lt;t&gt; &lt;p&gt; &lt;f&gt; } letters also
constitute a well-defined prev-class; but they cannot be the vowels,
because there is at most one gallows in each word, and half of the
words dont have any gallows. 

In fact, looking at the dnext() metric, one can readily see five or so
well-distinguished classes, that occupy rather constrained positions
with each word. These constraints are the basis for the crust-mantle-core model,
and there doesn't seem to be anything like it in European languages.

If the bizarre word structure was the only problem, we could get
around it by looking at more exotic languages. Indeed, standard
written Arabic has symbols that are constrained to occur only at the
beginning or at the end of a word. And if the &quot;words&quot; are actually
Chinese syllables with tones, we would indeed expect to see four of five
classes with fairly strict constraints about glyph order.  But...

#### VOYNICHESE SYMBOL CLASSES ARE INCONSISTENT 

... then we have another big problem: the two metrics dnext() and
dprev() are quite different, and the classes defined by one cut across
the classes of the other. 

For instance, looking at the /next-symbol/ distributions, we will find
that EVA &lt;a&gt; and &lt;i&gt; are fairly next-similar, because they all tend to
be followed by the a small set of letters (&lt;i&gt; chiefly, then &lt;l&gt;, &lt;r&gt;,
&lt;n&gt;, &lt;m&gt; in varying amounts). On the other hand, &lt;a&gt; and &lt;y&gt; are
extremely next-dissimilar, because &lt;y&gt; is hardly ever followed by
those letters. However, if we look the otehr way, we will see that &lt;a&gt;
and &lt;y&gt; are extremely prev-similar (preceded by space, &lt;d&gt;, &lt;o&gt;, &lt;e&gt;,
&lt;ch&gt;, &lt;sh&gt;, &lt;l&gt;, &lt;r&gt;, &lt;s&gt;, and the gallows), but very unlike &lt;i&gt;
(which can be preceded only by &lt;a&gt;, &lt;i&gt;, and &lt;o&gt;).

This inconsistency explains why Sukhotin's algorithm --- which, if you
recall, looks at both sides of each letter at once --- failed to
produce a convincing. While the Suppose we ask someone to divide a
deck of cards in exctly *two* piles, such that each pile is as
homogeneous as possible in *both* suit and value. The result will
necessarily be very inconsitent in both criteria: in the &quot;low, black&quot;
pile he will have to put many red-low and many black-high cards.

And indeed, while Sukhotin's algorithm consistently classifies as vowels the
symbols &lt;e&gt;, &lt;a&gt;, &lt;o&gt;, &lt;y&gt; (which *are* indeed either prev-similar or
next-similar to each other, and play vowel-like roles in the layer
model), it also reports a few odd symbols like &lt;s&gt;, &lt;p&gt;, and &lt;n&gt;,
which cannot possibly fit in the same class as those.  Moreover,
as said above, &lt;a&gt; and &lt;y&gt; are similar on one side only, and quite
unlike on the other.  Change the relative weights of dnext() and dprev() 
slightly, and the { &lt;e&gt;, &lt;a&gt;, &lt;o&gt;, &lt;y&gt; } class will fall apart.

#### SO WHAT IS NEW?

Those problems are probably old stuff. Anyone who has taken the time
to look at the next-letter and previous-letter tables must have
noticed the clear partition into the traditional letter classes
(gallows, dealers, etc.), but also must have become aware of (and
frustrated by) their peculiar positional constraints, and the
left-right inconsistency.  I myself have been chewing at that
bone since I discovered the VMS.

What I found out recently is that tehere seems to be some logic in the
left-right asymmetry after all. As you all know, most Voynichese
symbols are written with one or two two pen strokes, which we can
label &quot;left&quot; and &quot;right&quot; in writing order. In particular, the dealers
and circles are the result of combining either an &lt;e&gt; or &lt;i&gt; stroke on
the left, with one of eight strokes (plumes, flourishes, ligature, or
empty) on the right. Indeed, all but one of the 16 possible
combinations are valid characters:

       |0 |1 |2 |3 |4 |5 |6 |7 |8 
     --|--|--|--|--|--|--|--|--|--
     i |i |ii|* |l |r |j |m |n |C 
     e |e |a |o |y |s |d |g |b |I

The four gallows are similarly composed of two left halves and two
right halves, in all combinations. (Benches and platform gallows are
combinations of three or more strokes; let's leave them aside for the
moment.)

Now, the funny thing is that the next-symbol and prev-symbol
distributions seem to be determined in large part by the symbol's left
stroke and right stroke, respectively. In other words, symbols on the
same row of the table tend to be prev-similar, while symbols on the
same column tend to be next-similar.

Thus, for example, &lt;a&gt; and &lt;y&gt;, which have an &lt;e&gt; stroke on the left,
are fairly prev-similar but very next-different; while &lt;a&gt; and &lt;i&gt;,
which are have an &lt;i&gt; stroke on the right, are very next-similar but
quite prev-different.

In fact, this rule seems to explain most of the inconsistencies and
tantalizing hints that used to drive me crazy, such as the
relationship between the dealers { &lt;d&gt; &lt;l&gt; &lt;r&gt; &lt;s&gt; }, the finals { &lt;n&gt;
&lt;m&gt; &lt;g&gt; }, and the circles { &lt;a&gt; &lt;o&gt; &lt;y&gt; }. Now it seems almost
logical, for example, that &lt;l&gt; and &lt;y&gt; can be prefixed to gallows, but
&lt;r&gt; and &lt;s&gt; can't; while, on the other hand, &lt;d&gt; and &lt;s&gt; may follow
&lt;e&gt;, while &lt;l&gt; and &lt;r&gt; may not. Also, it makes sense that all the four
gallows &lt;k&gt; &lt;t&gt; &lt;p&gt; &lt;f&gt; are prev-similar, and yet &lt;k&gt; &lt;t&gt; are quite
next-different from &lt;p&gt; &lt;f&gt; (the latter are never followed by &lt;e&gt;).

To be sure, the above &quot;stroke harmony&quot; rule is not entirely
consistent. For instance, &lt;n&gt; and &lt;m&gt;, which both have an &lt;i&gt; stroke
on the left, are fairly prev-different --- &lt;n&gt; is almost always
preceded by &lt;i&gt;, while &lt;m&gt; (like &lt;r&gt; and &lt;l&gt;) are more often preceded
by &lt;a&gt;. But there is evidence anyway that &lt;in&gt; is a single letter, in
which case the inconsistency may well not exist. Also, &lt;d&gt; and &lt;s&gt; are
not as prev-similar to &lt;a&gt; as the theory would predict.

#### WHAT DOES IT MEAN?

There must be many theories that would explain this bizarre stroke
harmony rule. Here is only one of them. Suppose that instead of
writing the word &quot;HEREBY&quot; like this


    #   #    #####    ####     #####    ####     #   #
    #   #    #        #   #    #        #   #     # #
    #####    ####     ####     ####     ####       #
    #   #    #        #  #     #        #   #      #
    #   #    #####    #   #    #####    ####       #

a scribe were to detach the last stroke of each letter 
and attach it to the following letter, like this:

    #   #    #####    ####     #####    ####     #   #
    #   #    #        #   #    #        #   #     # #
    #   # #######     ####     ####     ####      ##
    #   #    #        #      # #        #          ##
    #   #    #    #####       ##    #####       ####

Note that there is now a strong correlation between the last few
strokes of each &quot;letter&quot; and the first stroke of the next one, as in
Voynichese --- simply because they were originally parts of the same
letter. (for instance, after a symbol with an &quot;F&quot;-like right side, one
would often find one with a &quot;foot&quot; stroke on the left side.) Note also
that different letters become equal, and equal letters become
different. Note also the tokens would continue to obey Zipf's law, but
all letter statistics would be fairly messed up.

Yet, no information has been lost; and, with a little practice, our
scribe could learn to write and read this &quot;grammar-school cipher&quot; as
quickly and naturally as plain text. However, even without realizing
it, he would have thrown a big monkey wrench into Sukhotin's algorithm
--- and, in fact, into most of our sophisticated statistical
methods...

I'd better stop here, and try get some sleep. All the best,

--stolfi

### Other works by Sukhotin

This list is also taken from voynich.net: http://www.voynich.net/Arch/2002/09/msg00050.html

Thanks to Luis Velez who pulled some references for works by Mr Sukhotin:

Luis mentions that:

A paper Sukhotin wrote called "Optimization algorithms of deciphering as the elements of a linguistic theory") by a
Boris Viktorivich Sukhotin was included in the12th International Conference On Computational Linguistics: COLING '88 (Budapest, 22-27 August 1988). 

Other works by him (available in Russian):

Title: Issledovanie grammatiki chislovymi metodami
by B. V. Sukhotin
ISBN: 5020110175
Publisher: "Nauka"

Author:    Sukhotin, B. V. (Boris Viktorovich)
Title:    Vydelenie morfem v tekstakh bez probelov mezhdu slovami / B.V.
Sukhotin ; otvetstvennyi redaktor A.A. Zalizniak.
Publisher:    Moskva : Izd-vo "Nauka", 1984.
Description:    94, [2] p. : ill. ; 22 cm.
Notes:    At head of title: Akademiia nauk SSSR. Institut russkogo iazyka.
Bibliography: p. [96]
Language:    Russian
Subjects:    Morphemics--Mathematical models
Morphemics--Data processing
Other entries:    Zalizniak, A. A. (Andrei Anatolevich)


Author:    Sukhotin, B. V. (Boris Viktorovich)
Title:    Optimizatsionnye metody issledovaniia iazyka / B. V. Sukhotin.
Publisher:    Moskva : Nauka, 1976.
Description:    168, [4] p. : ill. ; 21 cm.
Notes:    Romanized record.
At head of title: Akademiia nauk SSSR. Institut russkogo iazyka.
Bibliography: p. 168-[169]
Language:    Russian
Subjects:    Computational linguistics
Russian language--Data processing


Author:    Sukhotin, B. V. (Boris Viktorovich)
Title:    Issledovanie grammatiki chislovymi metodami / B.V. Sukhotin ;
otvetstvennyi redaktor A.A. Zalizniak.
Publisher:    Moskva : "Nauka", 1990.
Description:    175 p. : ill. ; 22 cm.
Notes:    At head of title: Akademiia nauk SSSR. Institut russkogo iazyka.
Summary in English.
"Nauchnoe izdanie"--T.p. verso.
Title on t.p. verso: Investigation of grammar by means of quantitative
modeling.
Includes bibliographical references (p. [173]).
ISBN:    5020110175 :
Language:    Russian
Subjects:    Mathematical linguistics
Other entries:    Zalizniak, A. A. (Andrei Anatolevich)
Investigation of grammar by means of quantitative modeling.

