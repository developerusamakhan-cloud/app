---
title: Methodology: How Our Calculators Work
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

## What we never do

- Promise a specific payout.
- Hide an assumption that lowers your number.
- Store or send the numbers you type.
