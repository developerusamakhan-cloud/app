---
title: Methodology: How Our Calculators Work
crumb: Methodology
slug: methodology
page_type: standard
order: 12
seo_title: Methodology: The Formulas Behind Our Claim Calculators
excerpt: Every formula and assumption behind the ClaimFairly calculators in one place: 17c diminished value, take-home math, settlement ranges, pain and suffering and fault rules.
---
Every result on ClaimFairly comes with its formula. This page collects all of them in one place, with the assumptions and the known weak spots. All math runs in your browser.

## Diminished value (17c method)

**Result = market value x 10% x damage multiplier x mileage multiplier**

| Damage | Multiplier | Mileage | Multiplier |
|---|---|---|---|
| Severe structural | 1.00 | 0 to 19,999 | 1.0 |
| Major structural and panel | 0.75 | 20,000 to 39,999 | 0.8 |
| Moderate structural and panel | 0.50 | 40,000 to 59,999 | 0.6 |
| Minor structural and panel | 0.25 | 60,000 to 79,999 | 0.4 |
| No structural damage | 0.00 | 80,000 to 99,999 | 0.2 |
| | | 100,000 and up | 0 |

**Known limits:** the 10% cap and the zero at 100,000 miles are not based on resale data. We present the result as the insurer's likely opening number, not a fair value. [Use the calculator](/diminished-value-calculator/).

## Settlement take-home

- **Fee from the full settlement:** fee = settlement x fee %; you keep = settlement minus fee minus costs minus liens.
- **Fee after costs:** fee = (settlement minus costs) x fee %; you keep = settlement minus costs minus fee minus liens.
- Liens are reduced by the lien reduction percentage you enter.

**Known limits:** does not include taxes, interest or structured settlements. [Use the calculator](/settlement-calculator-take-home/).

## Car accident settlement estimate

1. **Economic damages** = medical bills (past and future) + lost wages.
2. **Pain and suffering** = medical bills x a multiplier based on severity: minor 1.5 to 2, moderate 2 to 3, serious 3 to 4, severe 4 to 5.
3. **Low and high totals** = economic damages + the low or high pain and suffering figure.
4. **Fault adjustment** based on the state you choose (see the rules below).
5. **Policy limit:** if entered, both totals are capped at it.
6. **Property damage** is shown separately and is not added to the injury range.

How the fault adjustment works for each rule:

- **Pure comparative:** total x (1 minus your fault %).
- **Modified 50% bar:** zero at 50% fault or more, otherwise total x (1 minus fault %).
- **Modified 51% bar:** zero above 50% fault, otherwise total x (1 minus fault %).
- **Michigan:** economic damages x (1 minus fault %); pain and suffering is zero above 50% fault.
- **Contributory** (Alabama, Maryland, North Carolina, Virginia, DC): zero with any fault.
- **South Dakota** (slight vs gross): straight reduction, flagged in the result.

**Known limits:** real insurers use claims software that weighs medical codes and many other details. No calculator can know your evidence, credibility or venue. [Use the calculator](/car-accident-settlement-calculator/).

## Pain and suffering

- **Multiplier method** = medical costs x the severity range above.
- **Per diem method** = daily rate x recovery days. If you enter yearly income, we suggest a daily rate of income divided by 260 working days.
- The overall range runs from the lowest to the highest of both methods.

[Use the calculator](/pain-and-suffering-calculator/).

## State data

Each state's fault system, negligence rule, general injury filing deadline and minimum liability limits are stored in one data file with a "last checked" date. We check them against official sources and review them at least every three months. See the [state rules table](/states/).

## Demand letters

The demand letter generator fills a fixed template with the details you enter. It does not look up laws or add legal claims. The letter is created in your browser and the PDF is generated on your device.

## Why we show ranges instead of one number

Real claims settle across a range because they depend on evidence, credibility, the adjuster, the venue and negotiation. A single number would suggest a precision no calculator can offer. Ranges are more honest and more useful: they give you a realistic floor and ceiling to compare an offer against.

## Rounding and inputs

All money values are rounded to whole dollars for display. The calculators accept values like "12,500" or "12.5k." Percentages are applied exactly as entered. The take-home calculator's lien reduction is applied to the lien before it is subtracted. The settlement estimator applies the policy limit after the fault adjustment.

## Known limits of every calculator

- They cannot see your medical records, the police report or the other driver's statement.
- They do not include punitive damages, loss of consortium, interest or court costs.
- They use general state rules and cannot account for every exception.
- They assume the numbers you enter are accurate.

For decisions about your specific claim, talk to a licensed attorney in your state.

## What we never do

- Promise a specific payout.
- Hide an assumption that lowers your number.
- Store or send the numbers you type.

[cf_faq]
[cf_q q="Are the calculator formulas the same ones insurers use?"]They are common starting methods used in the industry, like the 17c formula and the multiplier method. Many insurers also use private claims software, so their results can differ.[/cf_q]
[cf_q q="Why does the diminished value calculator give such low numbers?"]Because it uses the 17c formula, which caps the loss at 10% and drops to zero at 100,000 miles. We show it as the insurer's likely opening number, not a fair value.[/cf_q]
[cf_q q="Where do the pain and suffering multipliers come from?"]The 1.5 to 5 range is a widely used rule of thumb in injury claims, scaled by injury severity. Real results vary.[/cf_q]
[cf_q q="Is my information sent to a server?"]No. All calculations run in your browser and nothing is stored.[/cf_q]
[cf_q q="How are state fault rules applied?"]Each state is assigned its negligence rule from our state data file, and the calculator applies that rule to your fault percentage. See the rules above.[/cf_q]
[/cf_faq]
